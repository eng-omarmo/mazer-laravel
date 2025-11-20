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
            <div class="col-12 col-md-6 order-md-2 order-first text-end">
                <a href="{{ route('hrm.employees.create') }}" class="btn btn-primary">Create</a>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th><i class="bi bi-person-badge me-2"></i>Name</th>
                            <th><i class="bi bi-envelope me-2"></i>Email</th>
                            <th><i class="bi bi-building me-2"></i>Department</th>
                            <th><i class="bi bi-activity me-2"></i>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                        <tr>
                            <td>{{ $employee->first_name }} {{ $employee->last_name }}</td>
                            <td>{{ $employee->email }}</td>
                            <td>{{ $employee->department?->name }}</td>
                            <td><span class="badge bg-primary">{{ ucfirst($employee->status) }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('hrm.employees.show',$employee) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('hrm.employees.edit',$employee) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('hrm.employees.destroy',$employee) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-end">
                    {{ $employees->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection