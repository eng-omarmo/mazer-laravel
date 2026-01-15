<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExpensePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        foreach (['expense.view', 'expense.approve', 'payment.initiate', 'payment.approve', 'payment.view_history', 'payment.cancel'] as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }
        $role = Role::firstOrCreate(['name' => 'finance']);
        $role->givePermissionTo(['expense.view', 'payment.approve']);
    }

    public function test_finance_can_approve_payment()
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $payment = \App\Models\ExpensePayment::factory()->create();

        $resp = $this->actingAs($user)->post(route('expense-payments.approve', $payment));
        $resp->assertStatus(302);
    }

    public function test_user_without_permission_cannot_approve_payment()
    {
        $user = User::factory()->create();

        $payment = \App\Models\ExpensePayment::factory()->create();

        $resp = $this->actingAs($user)->post(route('expense-payments.approve', $payment));
        $resp->assertStatus(403);
    }
}
