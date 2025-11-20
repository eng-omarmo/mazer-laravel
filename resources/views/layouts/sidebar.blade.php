<div class="sidebar-wrapper active">
    <div class="sidebar-header">
        <div class="d-flex justify-content-between">
            <div class="logo">
                <a href="{{ url('/') }}"><img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo"></a>
            </div>
            <div class="toggler">
                <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
            </div>
        </div>
    </div>
    <div class="sidebar-menu">
        <ul class="menu">
            <li class="sidebar-title">Menu</li>
            <li class="sidebar-item active">
                <a href="{{ url('/') }}" class='sidebar-link'>
                    <i class="bi bi-grid-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-title">HRM</li>
            <li class="sidebar-item has-sub">
                <a href="#" class='sidebar-link'>
                    <i class="bi bi-people"></i>
                    <span>People</span>
                </a>
                <ul class="submenu">
                    <li class="submenu-item">
                        <a href="{{ route('hrm.employees.index') }}">
                            <i class="bi bi-card-list me-2"></i> Employees
                        </a>
                    </li>
                    <li class="submenu-item">
                        <a href="{{ route('hrm.employees.create') }}">
                            <i class="bi bi-person-plus me-2"></i> Add Employee
                        </a>
                    </li>
                    <li class="submenu-item">
                        <a href="{{ route('hrm.departments.index') }}">
                            <i class="bi bi-diagram-3 me-2"></i> Department List
                        </a>
                    </li>
                    <li class="submenu-item">
                        <a href="{{ route('hrm.departments.create') }}">
                            <i class="bi bi-plus-circle me-2"></i> Add Department
                        </a>
                    </li>
                </ul>
            </li>
            <li class="sidebar-item has-sub">
                <a href="#" class='sidebar-link'>
                    <i class="bi bi-calendar-check"></i>
                    <span>Attendance</span>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="{{ url('hrm/attendance') }}">Attendance</a></li>
                    <li class="submenu-item"><a href="{{ url('hrm/leave') }}">Leave</a></li>
                </ul>
            </li>
            <li class="sidebar-item has-sub">
                <a href="#" class='sidebar-link'>
                    <i class="bi bi-graph-up"></i>
                    <span>Performance</span>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="{{ url('hrm/performance') }}">Reviews & Goals</a></li>
                    <li class="submenu-item"><a href="{{ url('hrm/reports') }}">Analytics & Reports</a></li>
                </ul>
            </li>
            <li class="sidebar-item has-sub">
                <a href="#" class='sidebar-link'>
                    <i class="bi bi-cash-stack"></i>
                    <span>Payroll</span>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="{{ url('hrm/payroll') }}">Payroll</a></li>
                </ul>
            </li>
            <li class="sidebar-item has-sub">
                <a href="#" class='sidebar-link'>
                    <i class="bi bi-briefcase"></i>
                    <span>Recruitment</span>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="{{ url('hrm/recruitment') }}">Candidates & Jobs</a></li>
                </ul>
            </li>
            <li class="sidebar-item has-sub">
                <a href="#" class='sidebar-link'>
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="{{ url('hrm/settings') }}">Configuration</a></li>
                </ul>
            </li>
        </ul>
    </div>
    <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
</div>