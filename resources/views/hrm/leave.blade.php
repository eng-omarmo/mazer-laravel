@extends('layouts.master')
@section('title','Leaves')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Employee Leaves</h3>
                <p class="text-subtitle text-muted">List and manage leave requests</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Leaves</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <form method="get" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            @foreach(['pending','approved','rejected'] as $s)
                                <option value="{{ $s }}" {{ request('status')===$s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <input type="text" name="type" value="{{ request('type') }}" class="form-control" placeholder="e.g. Annual">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select">
                            <option value="">All</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('employee_id')==$emp->id ? 'selected' : '' }}>{{ $emp->first_name }} {{ $emp->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-outline-primary"><i class="bi bi-funnel"></i> Filter</button>
                        <a href="{{ route('hrm.leave.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Reset</a>
                        <a href="{{ route('hrm.leave.create') }}" class="btn btn-primary float-end"><i class="bi bi-plus-lg"></i> Request Leave</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaves as $leave)
                                <tr>
                                    <td>{{ $leave->employee->first_name }} {{ $leave->employee->last_name }}</td>
                                    <td>{{ $leave->type }}</td>
                                    <td>{{ $leave->start_date }} → {{ $leave->end_date }}</td>
                                    <td><span class="badge bg-{{ $leave->status === 'approved' ? 'success' : ($leave->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($leave->status) }}</span></td>
                                    <td>
                                        <a href="{{ route('hrm.leave.edit', $leave) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil-square"></i> Edit</a>
                                        <form method="post" action="{{ route('hrm.leave.destroy', $leave) }}" class="d-inline">@csrf @method('delete')
                                            <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this leave?')"><i class="bi bi-trash"></i> Delete</button>
                                        </form>
                                        @if($leave->status === 'pending')
                                            <form method="post" action="{{ route('hrm.leave.approve', $leave) }}" class="d-inline">@csrf<button class="btn btn-success btn-sm"><i class="bi bi-check2-circle"></i> Approve</button></form>
                                            <button class="btn btn-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#reject-{{ $leave->id }}"><i class="bi bi-x-circle"></i> Reject</button>
                                            <div class="modal fade" id="reject-{{ $leave->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-x-circle"></i> Reject Leave</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                                        <form method="post" action="{{ route('hrm.leave.reject', $leave) }}">@csrf
                                                            <div class="modal-body">
                                                                <label class="form-label">Reason</label>
                                                                <textarea name="reason" class="form-control" rows="3"></textarea>
                                                            </div>
                                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger"><i class="bi bi-x-circle"></i> Reject</button></div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">No leave requests</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $leaves->links() }}
            </div>
        </div>
    </section>
</div>
@endsection