<div class="sidebar-wrapper active">
    <div class="sidebar-header">
        <div class="d-flex justify-content-between">
            <div class="logo">
                <a href="{{ route('dashboard') }}"><img src="{{ asset('assets/images/logo/logo.png') }}"
                        alt="Logo" /></a>
            </div>
            <div class="toggler">
                <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x"></i></a>
            </div>
        </div>
    </div>

    <div class="sidebar-menu">
        <ul class="menu">
            <li class="sidebar-title">Main</li>
            <li class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="sidebar-link">
                    <i class="bi bi-grid-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-title">HRM</li>

            @can('view organizations')
            <li class="sidebar-item has-sub {{ request()->is('hrm/organizations*') ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-building"></i>
                    <span>Organizations</span>
                </a>
                <ul class="submenu" style="display: none;">
                    <li class="submenu-item {{ request()->routeIs('hrm.organizations.index') ? 'active' : '' }}"><a
                            href="{{ route('hrm.organizations.index') }}"><i class="bi bi-card-list"></i> List</a></li>
                    @can('create organizations')
                    <li class="submenu-item {{ request()->routeIs('hrm.organizations.create') ? 'active' : '' }}"><a
                            href="{{ route('hrm.organizations.create') }}"><i class="bi bi-plus-circle"></i> Add</a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcan

            @can('view departments')
            <li class="sidebar-item has-sub {{ request()->is('hrm/departments') ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-diagram-3-fill"></i>
                    <span>Departments</span>
                </a>
                <ul class="submenu" style="display: none;">
                    <li class="submenu-item {{ request()->routeIs('hrm.departments.index') ? 'active' : '' }}"><a
                            href="{{ route('hrm.departments.index') }}"><i class="bi bi-card-list"></i> List</a></li>
                    @can('create departments')
                    <li class="submenu-item {{ request()->routeIs('hrm.departments.create') ? 'active' : '' }}"><a
                            href="{{ route('hrm.departments.create') }}"><i class="bi bi-calendar-plus"> </i> Add</a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcan
            @can('view employees')
            <li class="sidebar-item has-sub {{ request()->is('hrm/employees*') ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-people-fill"></i>
                    <span>Employees</span>
                </a>
                <ul class="submenu" style="display: none;">
                    <li class="submenu-item {{ request()->routeIs('hrm.employees.index') ? 'active' : '' }}"><a
                            href="{{ route('hrm.employees.index') }}"><i class="bi bi-card-list"></i> List</a></li>
                    @can('create employees')
                    <li class="submenu-item {{ request()->routeIs('hrm.employees.create') ? 'active' : '' }}"><a
                            href="{{ route('hrm.employees.create') }}"><i class="bi bi-person-plus"></i> Add</a></li>
                    @endcan
                </ul>
            </li>
            @endcan

            @can('view verification')
            <li class="sidebar-item has-sub {{ request()->is('hrm/verification') ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-check2-square"></i>
                    <span>Documents</span>
                </a>
                <ul class="submenu" style="display: none;">
                    <li class="submenu-item {{ request()->is('hrm/verification') ? 'active' : '' }}"><a
                            href="{{ route('hrm.verification.index') }}"><i class="bi bi-shield-check"></i>
                            Verification</a></li>
                </ul>
            </li>
            @endcan
            @can('view leaves')
            <li class="sidebar-item has-sub {{ request()->is('hrm/leave*') ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-calendar-check"></i>
                    <span>Leaves</span>
                </a>
                <ul class="submenu" style="display: none;">
                    <li class="submenu-item {{ request()->routeIs('hrm.leave.index') ? 'active' : '' }}"><a
                            href="{{ route('hrm.leave.index') }}"><i class="bi bi-card-list"></i> List</a></li>
                    @can('create leaves')
                    <li class="submenu-item {{ request()->routeIs('hrm.leave.create') ? 'active' : '' }}"><a
                            href="{{ route('hrm.leave.create') }}"><i class="bi bi-calendar-plus"></i> Add</a></li>
                    @endcan
                </ul>
            </li>
            @endcan
            @can('view payroll')
            <li class="sidebar-item has-sub {{ request()->is('hrm/payroll*') ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-cash-stack"></i>
                    <span>Payroll</span>
                </a>
                <ul class="submenu" style="display: none;">
                    <li class="submenu-item {{ request()->routeIs('hrm.payroll.index') ? 'active' : '' }}"><a
                            href="{{ route('hrm.payroll.index') }}"><i class="bi bi-card-list"></i> List</a></li>
                    @can('create payroll')
                    <li class="submenu-item {{ request()->routeIs('hrm.payroll.create') ? 'active' : '' }}"><a
                            href="{{ route('hrm.payroll.create') }}"><i class="bi bi-plus-circle"></i> Add</a></li>
                    @endcan
                    @can('view payroll batches')
                    <li class="submenu-item {{ request()->is('hrm/payroll/batches*') ? 'active' : '' }}"><a
                            href="{{ route('hrm.payroll.batches.index') }}"><i class="bi bi-collection"></i>
                            Batches</a></li>
                    @endcan
                    @can('create payroll batches')
                    <li class="submenu-item {{ request()->routeIs('hrm.payroll.batches.create') ? 'active' : '' }}"><a
                            href="{{ route('hrm.payroll.batches.create', ['preview' => 0]) }}"><i
                                class="bi bi-upload"></i> Post Payroll</a></li>
                    @endcan
                </ul>
            </li>
            @endcan

            @can('view advances')
            <li class="sidebar-item has-sub {{ request()->is('hrm/advances*') ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-cash"></i>
                    <span>Advances</span>
                </a>
                <ul class="submenu" style="display: none;">
                    <li class="submenu-item {{ request()->routeIs('hrm.advances.index') ? 'active' : '' }}"><a
                            href="{{ route('hrm.advances.index') }}"><i class="bi bi-card-list"></i> List</a></li>
                    @can('create advances')
                    <li class="submenu-item {{ request()->routeIs('hrm.advances.create') ? 'active' : '' }}"><a
                            href="{{ route('hrm.advances.create') }}"><i class="bi bi-plus-circle"></i> Add</a></li>
                    @endcan
                </ul>
            </li>
            @endcan



            @can('view wallet')
            <li class="sidebar-item {{ request()->routeIs('hrm.wallet.*') ? 'active' : '' }}">
                <a href="{{ route('hrm.wallet.index') }}" class="sidebar-link">
                    <i class="bi bi-wallet2"></i>
                    <span>Wallet</span>
                </a>
            </li>
            @endcan

            @can('view attendance')
            <li class="sidebar-item has-sub {{ request()->is('hrm/attendance*') ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-calendar-day"></i>
                    <span>Attendance</span>
                </a>
                <ul class="submenu" style="display: none;">
                    <li class="submenu-item {{ request()->routeIs('hrm.attendance.index') ? 'active' : '' }}"><a
                            href="{{ route('hrm.attendance.index') }}"><i class="bi bi-card-list"></i> Daily Logs</a>
                    </li>
                    @can('mark attendance')
                    <li class="submenu-item {{ request()->routeIs('hrm.attendance.create') ? 'active' : '' }}"><a
                            href="{{ route('hrm.attendance.create') }}"><i class="bi bi-plus-circle"></i> Mark
                            Attendance</a></li>
                    @endcan
                    @can('view attendance summary')
                    <li class="submenu-item {{ request()->routeIs('hrm.attendance.summary') ? 'active' : '' }}"><a
                            href="{{ route('hrm.attendance.summary') }}"><i class="bi bi-bar-chart"></i> Monthly
                            Summary</a></li>
                    @endcan

                </ul>
            </li>
            @endcan

            @can('view reports')
            <li class="sidebar-item has-sub {{ request()->is('hrm/reports*') ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-bar-chart"></i>
                    <span>Reports</span>
                </a>
                <ul class="submenu" style="display: none;">
                    <li class="submenu-item {{ request()->routeIs('hrm.reports.employees') ? 'active' : '' }}"><a
                            href="{{ route('hrm.reports.employees') }}"><i class="bi bi-people"></i> Employee</a>
                    </li>
                    <li class="submenu-item {{ request()->routeIs('hrm.reports.leaves') ? 'active' : '' }}"><a
                            href="{{ route('hrm.reports.leaves') }}"><i class="bi bi-calendar-check"></i> Leave</a>
                    </li>
                    <li class="submenu-item {{ request()->routeIs('hrm.reports.attendance') ? 'active' : '' }}"><a
                            href="{{ route('hrm.reports.attendance') }}"><i class="bi bi-calendar-day"></i>
                            Attendance</a></li>
                    <li class="submenu-item {{ request()->routeIs('hrm.reports.payroll') ? 'active' : '' }}"><a
                            href="{{ route('hrm.reports.payroll') }}"><i class="bi bi-cash-stack"></i> Payroll</a>
                    </li>
                    <!-- <li class="submenu-item {{ request()->routeIs('hrm.reports.expenses') ? 'active' : '' }}"><a
                            href="{{ route('hrm.reports.expenses') }}"><i class="bi bi-receipt"></i> Expenses</a>
                    </li> -->
                    <li class="submenu-item {{ request()->routeIs('hrm.reports.payments') ? 'active' : '' }}"><a
                            href="{{ route('hrm.reports.payments') }}"><i class="bi bi-credit-card"></i> Payments</a>
                    </li>
                </ul>
            </li>
            @endcan
            <!--
            @canany(['view suppliers', 'view expenses'])
            <li class="sidebar-item has-sub {{ (request()->is('hrm/suppliers*') || request()->is('hrm/expenses*')) ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-receipt"></i>
                    <span>Expense Management</span>
                </a>
                <ul class="submenu" style="display: none;">
                    @can('view suppliers')
                    <li class="submenu-item {{ request()->is('hrm/suppliers*') ? 'active' : '' }}"><a
                            href="{{ route('hrm.suppliers.index') }}"><i class="bi bi-truck"></i> Suppliers</a></li>
                    @endcan
                    @can('view expenses')
                    <li class="submenu-item {{ (request()->is('hrm/expenses*') && !request()->routeIs('hrm.expenses.payments.pending')) ? 'active' : '' }}"><a
                            href="{{ route('hrm.expenses.index') }}"><i class="bi bi-file-text"></i> Expenses</a></li>
                    @endcan
                    @can('view pending payments')
                    <li class="submenu-item {{ request()->routeIs('hrm.expenses.payments.pending') ? 'active' : '' }}"><a
                            href="{{ route('hrm.expenses.payments.pending') }}"><i class="bi bi-clock"></i> Pending Payments</a></li>
                    @endcan
                </ul>
            </li>
            @endcan -->

            @canany(['view users', 'view roles', 'view permissions', 'view api configs'])
            <li class="sidebar-title">Administration</li>

            @can('view users')
            <li class="sidebar-item has-sub {{ request()->is('admin/users*') ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-people"></i>
                    <span>Users</span>
                </a>
                <ul class="submenu" style="display: none;">
                    <li class="submenu-item {{ request()->routeIs('admin.users.index') ? 'active' : '' }}"><a
                            href="{{ route('admin.users.index') }}"><i class="bi bi-card-list"></i> List</a></li>
                    @can('create users')
                    <li class="submenu-item {{ request()->routeIs('admin.users.create') ? 'active' : '' }}"><a
                            href="{{ route('admin.users.create') }}"><i class="bi bi-plus-circle"></i> Add</a></li>
                    @endcan
                </ul>
            </li>
            @endcan

            @canany(['view roles', 'view permissions'])
            <li class="sidebar-item has-sub {{ request()->is('admin/roles*') || request()->is('admin/permissions*') ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-shield-lock"></i>
                    <span>Access Control</span>
                </a>
                <ul class="submenu" style="display: none;">
                    @can('view roles')
                    <li class="submenu-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"><a
                            href="{{ route('admin.roles.index') }}"><i class="bi bi-person-badge"></i> Roles</a></li>
                    @endcan
                    @can('view permissions')
                    <li class="submenu-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}"><a
                            href="{{ route('admin.permissions.index') }}"><i class="bi bi-key"></i> Permissions</a></li>
                    @endcan
                </ul>
            </li>
            @endcan

            @can('view api configs')
            <li class="sidebar-item has-sub {{ request()->is('admin/api-configurations*') ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-gear"></i>
                    <span>API Configurations</span>
                </a>
                <ul class="submenu" style="display: none;">
                    <li class="submenu-item {{ request()->routeIs('admin.api-configurations.index') ? 'active' : '' }}"><a
                            href="{{ route('admin.api-configurations.index') }}"><i class="bi bi-card-list"></i> List</a></li>
                    @can('create api configs')
                    <li class="submenu-item {{ request()->routeIs('admin.api-configurations.create') ? 'active' : '' }}"><a
                            href="{{ route('admin.api-configurations.create') }}"><i class="bi bi-plus-circle"></i> Add</a></li>
                    @endcan
                </ul>
            </li>
            @endcan
            @endcanany

            <li class="sidebar-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <a href="{{ route('profile.edit') }}" class="sidebar-link">
                    <i class="bi bi-person-circle"></i>
                    <span>Account</span>
                </a>
            </li>
            <li class="sidebar-item text-center">
                <button type="button" class="sidebar-link" id="logoutButton">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </button>
            </li>

            <form action="{{ route('logout') }}" method="POST" id="logoutForm" class="d-none">
                @csrf
            </form>

            <script>
                document.getElementById('logoutButton').addEventListener('click', function() {
                    document.getElementById('logoutForm').submit();
                });
            </script>


        </ul>
    </div>
</div>
