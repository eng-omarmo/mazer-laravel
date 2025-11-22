@extends('layouts.master')
@section('title','Employee Report')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Employee Report</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Employees</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <form method="get" action="{{ route('hrm.reports.employees') }}" class="row g-2">
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
                            <option value="active" {{ request('status')==='active'?'selected':'' }}>Active</option>
                            <option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Search Name</label>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="e.g. John">
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i> Filter</button>
                        <a href="{{ route('hrm.reports.employees.csv') }}" class="btn btn-outline-primary"><i class="bi bi-download"></i></a>
                    </div>
                </form>
                <div class="row mt-3">
                    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Active</h6><div class="h4">{{ $activeCount }}</div></div></div></div>
                    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Inactive</h6><div class="h4">{{ $inactiveCount }}</div></div></div></div>
                    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Compliance %</h6><div class="h4">{{ $complianceRate }}%</div></div></div></div>
                </div>
                <h6 class="mt-3">Headcount by Department</h6>
                <div class="table-responsive">
                    <table class="table table-striped"><thead><tr><th>Department</th><th>Count</th></tr></thead><tbody>
                        @foreach($headcount as $hc)
                            <tr><td>{{ $hc['department'] }}</td><td>{{ $hc['count'] }}</td></tr>
                        @endforeach
                    </tbody></table>
                </div>
                <h6 class="mt-3">Employees</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Name</th><th>Department</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($employees as $e)
                                <tr>
                                    <td>{{ $e->first_name }} {{ $e->last_name }}</td>
                                    <td>{{ optional($e->department)->name }}</td>
                                    <td>{{ ucfirst($e->status) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $employees->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection