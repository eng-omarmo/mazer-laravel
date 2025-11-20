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

                <div class="mb-3">
                    <a href="{{ route('hrm.leave.create') }}" class="btn btn-primary">Request Leave</a>
                </div>

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
                                        @if($leave->status === 'pending')
                                            <form method="post" action="{{ route('hrm.leave.approve', $leave) }}" class="d-inline">@csrf<button class="btn btn-success btn-sm">Approve</button></form>
                                            <button class="btn btn-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#reject-{{ $leave->id }}">Reject</button>
                                            <div class="modal fade" id="reject-{{ $leave->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header"><h5 class="modal-title">Reject Leave</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                                        <form method="post" action="{{ route('hrm.leave.reject', $leave) }}">@csrf
                                                            <div class="modal-body">
                                                                <label class="form-label">Reason</label>
                                                                <textarea name="reason" class="form-control" rows="3"></textarea>
                                                            </div>
                                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Reject</button></div>
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