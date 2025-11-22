<div class="sidebar-wrapper active">
    <div class="sidebar-header">
        <div class="d-flex justify-content-between">
            <div class="logo">
                <a href="{{ route('dashboard') }}"><img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo" /></a>
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

            <li class="sidebar-item has-sub {{ request()->is('hrm/departments') ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-diagram-3-fill"></i>
                    <span>Departments</span>
                </a>
                <ul class="submenu" style="display: none;">
                    <li class="submenu-item {{ request()->routeIs('hrm.departments.index') ? 'active' : '' }}"><a href="{{ route('hrm.departments.index') }}"><i class="bi bi-card-list"></i> List</a></li>
                    <li class="submenu-item {{ request()->routeIs('hrm.departments.create') ? 'active' : '' }}"><a href="{{ route('hrm.departments.create') }}"><i class="bi bi-calendar-plus"> </i> Add</a></li>
                </ul>
            </li>
            <li class="sidebar-item has-sub {{ request()->is('hrm/employees*') ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-people-fill"></i>
                    <span>Employees</span>
                </a>
                <ul class="submenu" style="display: none;">
                    <li class="submenu-item {{ request()->routeIs('hrm.employees.index') ? 'active' : '' }}"><a href="{{ route('hrm.employees.index') }}"><i class="bi bi-card-list"></i> List</a></li>
                    <li class="submenu-item {{ request()->routeIs('hrm.employees.create') ? 'active' : '' }}"><a href="{{ route('hrm.employees.create') }}"><i class="bi bi-person-plus"></i> Add</a></li>
                </ul>
            </li>

            <li class="sidebar-item has-sub {{ request()->is('hrm/verification') ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-check2-square"></i>
                    <span>Documents</span>
                </a>
                <ul class="submenu" style="display: none;">
                    <li class="submenu-item {{ request()->is('hrm/verification') ? 'active' : '' }}"><a href="{{ route('hrm.verification.index') }}"><i class="bi bi-shield-check"></i> Verification</a></li>
                </ul>
            </li>
            <li class="sidebar-item has-sub {{ request()->is('hrm/leave*') ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-calendar-check"></i>
                    <span>Leaves</span>
                </a>
                <ul class="submenu" style="display: none;">
                    <li class="submenu-item {{ request()->routeIs('hrm.leave.index') ? 'active' : '' }}"><a href="{{ route('hrm.leave.index') }}"><i class="bi bi-card-list"></i> List</a></li>
                    <li class="submenu-item {{ request()->routeIs('hrm.leave.create') ? 'active' : '' }}"><a href="{{ route('hrm.leave.create') }}"><i class="bi bi-calendar-plus"></i> Add</a></li>
                </ul>
            </li>

            <li class="sidebar-item has-sub {{ request()->is('hrm/payroll*') ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-cash-stack"></i>
                    <span>Payroll</span>
                </a>
                <ul class="submenu" style="display: none;">
                    <li class="submenu-item {{ request()->routeIs('hrm.payroll.index') ? 'active' : '' }}"><a href="{{ route('hrm.payroll.index') }}"><i class="bi bi-card-list"></i> List</a></li>
                    <li class="submenu-item {{ request()->routeIs('hrm.payroll.create') ? 'active' : '' }}"><a href="{{ route('hrm.payroll.create') }}"><i class="bi bi-plus-circle"></i> Add</a></li>
                    <li class="submenu-item {{ request()->is('hrm/payroll/batches*') ? 'active' : '' }}"><a href="{{ route('hrm.payroll.batches.index') }}"><i class="bi bi-collection"></i> Batches</a></li>
                    <li class="submenu-item {{ request()->routeIs('hrm.payroll.batches.create') ? 'active' : '' }}"><a href="{{ route('hrm.payroll.batches.create', ['preview' => 0]) }}"><i class="bi bi-upload"></i> Post Payroll</a></li>
                </ul>
            </li>

            <li class="sidebar-item {{ request()->routeIs('hrm.wallet.*') ? 'active' : '' }}">
                <a href="{{ route('hrm.wallet.index') }}" class="sidebar-link">
                    <i class="bi bi-wallet2"></i>
                    <span>Wallet</span>
                </a>
            </li>

            

            <li class="sidebar-item has-sub {{ request()->is('hrm/attendance*') ? 'active' : '' }}">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-calendar-day"></i>
                    <span>Attendance</span>
                </a>
                <ul class="submenu" style="display: none;">
                    <li class="submenu-item {{ request()->routeIs('hrm.attendance.index') ? 'active' : '' }}"><a href="{{ route('hrm.attendance.index') }}"><i class="bi bi-card-list"></i> Daily Logs</a></li>
                    <li class="submenu-item {{ request()->routeIs('hrm.attendance.create') ? 'active' : '' }}"><a href="{{ route('hrm.attendance.create') }}"><i class="bi bi-plus-circle"></i> Mark Attendance</a></li>
                    <li class="submenu-item {{ request()->routeIs('hrm.attendance.summary') ? 'active' : '' }}"><a href="{{ route('hrm.attendance.summary') }}"><i class="bi bi-bar-chart"></i> Monthly Summary</a></li>

                </ul>
            </li>

            <li class="sidebar-title">Administration</li>

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
