<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        // Create Admin User
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        // Ensure admin user has admin role (since factory might not trigger the seeder logic for new users automatically unless we explicitly assign it or rely on the role column)
        // In this app, we are migrating to Spatie roles, so let's assign it.
        $this->adminUser->assignRole('admin');
    }

    public function test_admin_can_view_roles()
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.roles.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.roles.index');
    }

    public function test_admin_can_create_role()
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.roles.store'), [
            'name' => 'test-role',
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', ['name' => 'test-role']);
    }

    public function test_admin_can_create_role_with_permissions()
    {
        $permission = Permission::create(['name' => 'test-permission']);

        $response = $this->actingAs($this->adminUser)->post(route('admin.roles.store'), [
            'name' => 'test-role-with-perm',
            'permissions' => [$permission->id],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $role = Role::where('name', 'test-role-with-perm')->first();
        $this->assertTrue($role->hasPermissionTo('test-permission'));
    }

    public function test_admin_can_update_role()
    {
        $role = Role::create(['name' => 'old-role']);

        $response = $this->actingAs($this->adminUser)->put(route('admin.roles.update', $role), [
            'name' => 'new-role-name',
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'new-role-name']);
    }

    public function test_admin_can_delete_role()
    {
        $role = Role::create(['name' => 'delete-me']);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.roles.destroy', $role));

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_cannot_delete_admin_role()
    {
        $role = Role::where('name', 'admin')->firstOrFail();

        $response = $this->actingAs($this->adminUser)->delete(route('admin.roles.destroy', $role));

        $this->assertDatabaseHas('roles', ['name' => 'admin']);
    }

    public function test_admin_can_create_permission()
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.permissions.store'), [
            'name' => 'new-permission',
        ]);

        $response->assertRedirect(route('admin.permissions.index'));
        $this->assertDatabaseHas('permissions', ['name' => 'new-permission']);
    }

    public function test_validation_prevents_duplicate_roles()
    {
        Role::create(['name' => 'existing-role']);

        $response = $this->actingAs($this->adminUser)->post(route('admin.roles.store'), [
            'name' => 'existing-role',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_user_role()
    {
        $user = User::factory()->create(['role' => 'hrm']);
        $user->assignRole('hrm');

        $financeRole = Role::where('name', 'finance')->first();

        $response = $this->actingAs($this->adminUser)->patch(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $financeRole->id, // Send ID
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $user->refresh();
        $this->assertEquals('finance', $user->role); // Legacy column
        $this->assertTrue($user->hasRole('finance')); // Spatie role
        $this->assertFalse($user->hasRole('hrm')); // Should be synced (replaced)
    }
}
