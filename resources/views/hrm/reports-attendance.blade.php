@extends('layouts.master')
@section('title','Attendance Report')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Attendance Report</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Attendance</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <form method="get" action="{{ route('hrm.reports.attendance') }}" class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">All</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="present" {{ request('status')==='present'?'selected':'' }}>Present</option>
                            <option value="absent" {{ request('status')==='absent'?'selected':'' }}>Absent</option>
                            <option value="late" {{ request('status')==='late'?'selected':'' }}>Late</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From</label>
                        <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To</label>
                        <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i> Filter</button>
                        <a href="{{ route('hrm.attendance.export.csv') }}" class="btn btn-outline-primary" title="Export CSV"><i class="bi bi-download"></i></a>
                    </div>
                </form>
                <div class="row">
                    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Attendance Rate</h6><div class="h4">{{ $attendanceRate }}%</div></div></div></div>
                </div>
                <h6 class="mt-3">Daily Logs</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Date</th><th>Employee</th><th>Department</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->date }}</td>
                                    <td>{{ optional($log->employee)->first_name }} {{ optional($log->employee)->last_name }}</td>
                                    <td>{{ optional(optional($log->employee)->department)->name }}</td>
                                    <td>{{ ucfirst($log->status) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $logs->links() }}
                </div>
                <h6 class="mt-3">Late Occurrences</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Employee ID</th><th>Count</th></tr></thead>
                        <tbody>
                            @foreach($lateCounts as $lc)
                                <tr><td>{{ $lc->employee_id }}</td><td>{{ $lc->c }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <h6 class="mt-3">Absence Trends (last 30 days)</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Date</th><th>Count</th></tr></thead>
                        <tbody>
                            @foreach($absenceCounts as $ac)
                                <tr><td>{{ $ac->date }}</td><td>{{ $ac->c }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection