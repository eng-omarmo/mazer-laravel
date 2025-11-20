@extends('layouts.master')
@section('title','Employees')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Employees</h3>
                <p class="text-subtitle text-muted">Manage employee records</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Employees</li>
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
                    <a href="{{ route('hrm.employees.create') }}" class="btn btn-primary">Onboard Employee</a>
                    <a href="{{ route('hrm.verification.index') }}" class="btn btn-outline-secondary">Document Verification</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Hire Date</th>
                                <th>CV</th>
                                <th>Contract</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $emp)
                                @php
                                    $cv = $emp->documents->firstWhere('type','cv');
                                    $ct = $emp->documents->firstWhere('type','contract');
                                @endphp
                                <tr>
                                    <td>{{ $emp->first_name }} {{ $emp->last_name }}</td>
                                    <td>{{ $emp->email }}</td>
                                    <td>{{ optional($emp->department)->code }} {{ optional($emp->department) ? '-' : '' }} {{ optional($emp->department)->name }}</td>
                                    <td>{{ $emp->position }}</td>
                                    <td>{{ $emp->hire_date }}</td>
                                    <td>
                                        @if($cv)
                                            <span class="badge bg-{{ $cv->status === 'approved' ? 'success' : ($cv->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($cv->status) }}</span>
                                            <a class="ms-2" href="{{ asset('storage/'.$cv->path) }}" target="_blank">View</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($ct)
                                            <span class="badge bg-{{ $ct->status === 'approved' ? 'success' : ($ct->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($ct->status) }}</span>
                                            <a class="ms-2" href="{{ asset('storage/'.$ct->path) }}" target="_blank">View</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('hrm.employees.show', $emp) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-eye"></i> Show</a>
                                        <a href="{{ route('hrm.employees.edit', $emp) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil-square"></i> Edit</a>
                                        <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#assign-{{ $emp->id }}"><i class="bi bi-diagram-3"></i> Assign</button>

                                        <div class="modal fade" id="assign-{{ $emp->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><i class="bi bi-diagram-3"></i> Assign Department</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="post" action="{{ route('hrm.employees.update', $emp) }}">
                                                        @csrf
                                                        @method('patch')
                                                        <div class="modal-body">
                                                            <label class="form-label">Department</label>
                                                            <select name="department_id" class="form-select">
                                                                <option value="">None</option>
                                                                @foreach($departments as $dept)
                                                                    <option value="{{ $dept->id }}" {{ $emp->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->code }} - {{ $dept->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success">Assign</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">No employees</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $employees->links() }}
            </div>
        </div>
    </section>
</div>
@endsection