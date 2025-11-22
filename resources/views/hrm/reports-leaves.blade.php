@extends('layouts.master')
@section('title','Leave Report')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Leave Report</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Leaves</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <form method="get" action="{{ route('hrm.reports.leaves') }}" class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="pending" {{ request('status')==='pending'?'selected':'' }}>Pending</option>
                            <option value="approved" {{ request('status')==='approved'?'selected':'' }}>Approved</option>
                            <option value="rejected" {{ request('status')==='rejected'?'selected':'' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <input type="text" name="type" class="form-control" value="{{ request('type') }}" placeholder="e.g. annual">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select">
                            <option value="">All</option>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}" {{ request('employee_id')==$e->id?'selected':'' }}>{{ $e->first_name }} {{ $e->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i> Filter</button>
                        <a href="{{ route('hrm.reports.leaves.csv') }}" class="btn btn-outline-primary"><i class="bi bi-download"></i></a>
                    </div>
                </form>
                <div class="row mt-3">
                    <div class="col-md-4"><div class="card"><div class="card-body"><h6>Approval Rate</h6><div class="h4">{{ $approvalRate }}%</div></div></div></div>
                    <div class="col-md-4"><div class="card"><div class="card-body"><h6>Avg Duration</h6><div class="h4">{{ $avgDuration }} days</div></div></div></div>
                    <div class="col-md-4"><div class="card"><div class="card-body"><h6>Avg Pending Aging</h6><div class="h4">{{ $avgPendingAging }} days</div></div></div></div>
                </div>
                <h6 class="mt-3">Leaves</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Employee</th><th>Type</th><th>Status</th><th>Start</th><th>End</th><th>Days</th></tr></thead>
                        <tbody>
                            @foreach($leaves as $l)
                                <tr>
                                    <td>{{ optional($l->employee)->first_name }} {{ optional($l->employee)->last_name }}</td>
                                    <td>{{ $l->type }}</td>
                                    <td>{{ ucfirst($l->status) }}</td>
                                    <td>{{ $l->start_date }}</td>
                                    <td>{{ $l->end_date }}</td>
                                    <td>{{ $l->start_date && $l->end_date ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $leaves->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
