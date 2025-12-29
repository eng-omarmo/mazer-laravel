<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Dashboard
            'view dashboard',

            // HRM - Organizations
            'view organizations', 'create organizations', 'edit organizations', 'delete organizations',

            // HRM - Departments
            'view departments', 'create departments', 'edit departments', 'delete departments',

            // HRM - Employees
            'view employees', 'create employees', 'edit employees', 'delete employees',

            // HRM - Documents
            'view documents', 'verify documents', 'approve documents', 'reject documents',

            // HRM - Leaves
            'view leaves', 'create leaves', 'edit leaves', 'approve leaves',

            // HRM - Payroll
            'view payroll', 'create payroll', 'process payroll',

            // HRM - Advances
            'view advances', 'create advances', 'edit advances', 'approve advances',

            // HRM - Wallet
            'view wallet', 'manage wallet',

            // HRM - Attendance
            'view attendance', 'mark attendance', 'view attendance summary',

            // HRM - Reports
            'view reports',

            // Expense Management
            'view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers',
            'view expenses', 'create expenses', 'edit expenses', 'approve expenses', 'pay expenses',
            'view pending payments',

            // Administration
            'view users', 'create users', 'edit users', 'delete users',
            'view roles', 'create roles', 'edit roles', 'delete roles',
            'view permissions', 'create permissions', 'edit permissions', 'delete permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles and Assign Permissions

        // Admin
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // HRM (Human Resource Manager)
        $hrmRole = Role::firstOrCreate(['name' => 'hrm']);
        $hrmPermissions = [
            'view dashboard',
            'view organizations', 'create organizations', 'edit organizations',
            'view departments', 'create departments', 'edit departments',
            'view employees', 'create employees', 'edit employees',
            'view documents', 'verify documents', 'approve documents', 'reject documents',
            'view leaves', 'create leaves', 'edit leaves', 'approve leaves',
            'view payroll', 'create payroll', 'process payroll',
            'view advances', 'create advances', 'edit advances', 'approve advances',
            'view wallet', 'manage wallet',
            'view attendance', 'mark attendance', 'view attendance summary',
            'view reports',
        ];
        $hrmRole->givePermissionTo($hrmPermissions);

        // Credit Manager (Initiates expenses)
        $creditManagerRole = Role::firstOrCreate(['name' => 'credit_manager']);
        $creditManagerPermissions = [
            'view dashboard',
            'view expenses', 'create expenses', 'edit expenses',
            'view suppliers', 'create suppliers',
        ];
        $creditManagerRole->givePermissionTo($creditManagerPermissions);

        // Finance (Reviews expenses, Initiates payments)
        $financeRole = Role::firstOrCreate(['name' => 'finance']);
        $financePermissions = [
            'view dashboard',
            'view expenses', 'edit expenses', 'approve expenses', // 'approve' here contextually means 'review'
            'view suppliers',
            'view pending payments',
            'pay expenses',
            'view reports',
        ];
        $financeRole->givePermissionTo($financePermissions);

        // Assign Roles to existing users based on their 'role' column
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            // Map legacy role column to Spatie role
            // Assuming the 'role' column values match the role names created above (admin, hrm, credit_manager, finance)
            // If the user has a role string that matches a Spatie role, assign it.
            if ($user->role && Role::where('name', $user->role)->exists()) {
                $user->assignRole($user->role);
            } elseif ($user->role === 'manager') {
                // Example mapping if needed, though 'manager' wasn't explicitly mentioned as a Spatie role above
                // Maybe map to HRM? Or just leave it.
                // For now, assume exact match or simple mapping
            }
        }
    }
}
