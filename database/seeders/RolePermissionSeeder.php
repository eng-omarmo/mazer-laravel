<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // Employee Management
            'view employees',
            'create employees',
            'edit employees',
            'delete employees',
            
            // Department Management
            'view departments',
            'create departments',
            'edit departments',
            'delete departments',
            
            // Leave Management
            'view leaves',
            'create leaves',
            'edit leaves',
            'delete leaves',
            'approve leaves',
            'reject leaves',
            
            // Payroll Management
            'view payroll',
            'create payroll',
            'edit payroll',
            'delete payroll',
            'approve payroll',
            'mark payroll paid',
            
            // Payroll Batch Management
            'view payroll batches',
            'create payroll batches',
            'edit payroll batches',
            'submit payroll batches',
            'approve payroll batches',
            'reject payroll batches',
            'mark batch paid',
            
            // Attendance Management
            'view attendance',
            'create attendance',
            'edit attendance',
            'view attendance summary',
            'export attendance',
            
            // Employee Advances
            'view advances',
            'create advances',
            'approve advances',
            'mark advance paid',
            'view advance receipts',
            
            // Document Verification
            'view verification',
            'approve documents',
            'reject documents',
            
            // Expense Management
            'view expenses',
            'create expenses',
            'edit expenses',
            'delete expenses',
            'review expenses',
            'approve expenses',
            'pay expenses',
            
            // Supplier Management
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            'delete suppliers',
            
            // Organization Management
            'view organizations',
            'create organizations',
            'edit organizations',
            'delete organizations',
            
            // Wallet Management
            'view wallet',
            'deposit wallet',
            
            // Reports
            'view employee reports',
            'view leave reports',
            'view attendance reports',
            'view payroll reports',
            'view expense reports',
            'view advance reports',
            'export reports',
            
            // User Management (Admin only)
            'view users',
            'create users',
            'edit users',
            'delete users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $hrmRole = Role::firstOrCreate(['name' => 'hrm']);
        $hrmRole->givePermissionTo([
            'view employees',
            'create employees',
            'edit employees',
            'view departments',
            'create departments',
            'edit departments',
            'delete departments',
            'view leaves',
            'create leaves',
            'edit leaves',
            'approve leaves',
            'reject leaves',
            'view payroll',
            'create payroll',
            'edit payroll',
            'view payroll batches',
            'create payroll batches',
            'edit payroll batches',
            'submit payroll batches',
            'view attendance',
            'create attendance',
            'edit attendance',
            'view attendance summary',
            'export attendance',
            'view advances',
            'create advances',
            'approve advances',
            'view verification',
            'approve documents',
            'reject documents',
            'view expenses',
            'create expenses',
            'edit expenses',
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            'view organizations',
            'create organizations',
            'edit organizations',
            'view wallet',
            'view employee reports',
            'view leave reports',
            'view attendance reports',
            'view payroll reports',
            'export reports',
        ]);

        $financeRole = Role::firstOrCreate(['name' => 'finance']);
        $financeRole->givePermissionTo([
            'view payroll',
            'view payroll batches',
            'approve payroll batches',
            'mark batch paid',
            'view expenses',
            'review expenses',
            'approve expenses',
            'pay expenses',
            'view wallet',
            'deposit wallet',
            'view payroll reports',
            'view expense reports',
            'export reports',
        ]);

        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $employeeRole->givePermissionTo([
            'view employees', // Can view own profile
            'view leaves',
            'create leaves',
            'view attendance',
            'view advances',
            'view advance receipts',
        ]);

        $creditManagerRole = Role::firstOrCreate(['name' => 'credit_manager']);
        $creditManagerRole->givePermissionTo([
            'view expenses',
            'review expenses',
            'approve expenses',
            'view expense reports',
        ]);

        // Assign roles to existing users based on their current 'role' field
        $users = User::all();
        foreach ($users as $user) {
            $oldRole = strtolower($user->role ?? 'employee');
            
            // Map old role names to new Spatie roles
            $roleMap = [
                'admin' => 'admin',
                'hrm' => 'hrm',
                'hr' => 'hrm',
                'finance' => 'finance',
                'credit_manager' => 'credit_manager',
                'employee' => 'employee',
            ];
            
            $newRole = $roleMap[$oldRole] ?? 'employee';
            
            if (!$user->hasRole($newRole)) {
                $user->assignRole($newRole);
            }
        }

        $this->command->info('Roles and permissions created successfully!');
    }
}
