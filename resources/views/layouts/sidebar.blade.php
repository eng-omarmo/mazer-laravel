<div class="sidebar-wrapper active">
    <div class="sidebar-header">
        <div class="d-flex justify-content-between">
            <div class="logo">
                <a href="{{ route('dashboard') }}"><img src="{{ asset('assets/images/logo/logo.svg') }}" alt="Logo" /></a>
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

            <li class="sidebar-title">Administration</li>

            <li class="sidebar-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <a href="{{ route('profile.edit') }}" class="sidebar-link">
                    <i class="bi bi-person-circle"></i>
                    <span>Account</span>
                </a>
            </li>
        </ul>
    </div>
</div>
