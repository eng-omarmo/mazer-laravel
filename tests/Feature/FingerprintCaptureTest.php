<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\BiometricTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FingerprintCaptureTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->adminUser->assignRole('admin');
        Auth::login($this->adminUser);

        // Create a dummy employee
        $this->employee = Employee::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'salary' => 1000,
            'status' => 'active',
            'contract' => 'dummy', // Assuming simple string or file path mock
            'cv' => 'dummy',
        ]);
    }

    public function test_handshake_and_capture_creates_biometric_template_record(): void
    {
        Http::fake([
            '*/handshake' => Http::response(['ok' => true], 200),
            '*/capture' => Http::response([
                'ok' => true,
                'template' => 'FAKE_TEMPLATE_DATA',
                'dpi' => 500,
                'quality' => 80,
                'device_sn' => 'ZK-123456',
                'algorithm' => 'ZKTemplateV10',
            ], 200),
        ]);

        config(['app.biometric_key' => base64_encode(random_bytes(32))]);

        $response = $this->actingAs($this->adminUser)->post(route('hrm.fingerprint.capture'), [
            'employee_id' => $this->employee->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);

        $this->assertDatabaseCount('biometric_templates', 1);
        $record = BiometricTemplate::first();
        $this->assertEquals($this->employee->id, $record->employee_id);
        $this->assertEquals(500, $record->dpi);
        $this->assertEquals(80, $record->quality_score);
        $this->assertEquals('ZK-123456', $record->device_sn);
        $this->assertEquals('ZKTemplateV10', $record->algorithm);
        $this->assertNotEmpty($record->ciphertext);
        $this->assertNotEmpty($record->iv);
        $this->assertNotEmpty($record->tag);

        $this->employee->refresh();
        $this->assertEquals($record->id, $this->employee->fingerprint_id);
    }
}

    