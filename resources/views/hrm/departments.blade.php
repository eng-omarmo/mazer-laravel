@extends('layouts.master')
@section('title','Departments')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Departments</h3>
                <p class="text-subtitle text-muted">Manage  departments</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>

                        <li class="breadcrumb-item active" aria-current="page">Departments</li>
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
                    <a href="{{ route('hrm.departments.create') }}" class="btn btn-primary">Add Department</a>
             
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Department</th>
                                <th>Head</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departments as $dept)
                                <tr>
                                    <td>{{ $dept->code }}</td>
                                    <td>{{ $dept->name }}</td>
                                    <td>{{ optional($dept->head)->first_name }} {{ optional($dept->head)->last_name }}</td>
                                    <td>
                                        <a href="{{ route('hrm.departments.show', $dept) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-eye"></i> Show</a>
                                        <a href="{{ route('hrm.departments.edit', $dept) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil-square"></i> Edit</a>
                                        <form method="post" action="{{ route('hrm.departments.destroy', $dept) }}" class="d-inline">@csrf @method('delete')
                                            <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this department?')"><i class="bi bi-trash"></i> Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center">No departments</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $departments->links() }}
            </div>
        </div>
    </section>
</div>
@endsection
