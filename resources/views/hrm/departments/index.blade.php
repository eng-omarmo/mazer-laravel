@extends('layouts.master')
@section('title','Departments')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Departments</h3>
                <p class="text-subtitle text-muted">Manage organization departments</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first text-end">
                <a href="{{ route('hrm.departments.create') }}" class="btn btn-primary">Create</a>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th><i class="bi bi-diagram-3 me-2"></i>Name</th>
                            <th><i class="bi bi-text-paragraph me-2"></i>Description</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departments as $department)
                        <tr>
                            <td>{{ $department->name }}</td>
                            <td>{{ $department->description }}</td>
                            <td class="text-end">
                                <a href="{{ route('hrm.departments.show',$department) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('hrm.departments.edit',$department) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('hrm.departments.destroy',$department) }}" method="POST" class="d-inline">
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
                    {{ $departments->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection