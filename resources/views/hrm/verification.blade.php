@extends('layouts.master')
@section('title','Document Verification')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Document Verification</h3>
                <p class="text-subtitle text-muted">Approve or reject employee CV and contracts</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('hrm.employees.index') }}">Employees</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Verification</li>
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

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Uploaded</th>
                                <th>File</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingDocuments as $doc)
                            <tr>
                                <td>{{ $doc->employee->first_name }} {{ $doc->employee->last_name }}</td>
                                <td>{{ ucfirst($doc->type) }}</td>
                                <td>{{ $doc->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <span class="badge bg-{{
                                            $doc->status === 'approved' ? 'success' :
                                            ($doc->status === 'rejected' ? 'danger' : 'warning')
                                        }} ms-2">
                                        {{ ucfirst($doc->status) }}
                                    </span>
                                </td>
                                <td><a href="{{ asset('storage/'.$doc->path) }}" target="_blank">View</a></td>
                                <td>

                                    <a href="{{ route('hrm.employees.show', $doc->employee) }}" class="btn btn-primary btn-sm">Show</a>
                                    <form method="post" action="{{ route('hrm.verification.approve', $doc) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-success btn-sm" type="submit">Approve</button>
                                    </form>
                                    <button class="btn btn-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#reject-{{ $doc->id }}">Reject</button>

                                    <div class="modal fade" id="reject-{{ $doc->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reject Document</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="post" action="{{ route('hrm.verification.reject', $doc) }}">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <label class="form-label">Reason</label>
                                                        <textarea name="reason" class="form-control" rows="3"></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Reject</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No pending documents</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $pendingDocuments->links() }}
            </div>
        </div>
    </section>
</div>
@endsection
