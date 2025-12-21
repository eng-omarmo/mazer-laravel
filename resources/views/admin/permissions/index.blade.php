@extends('layouts.master')
@section('title', 'Permissions')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Permissions</h3>
                <p class="text-subtitle text-muted">Manage system permissions</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Permissions</li>
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
                    @can('create permissions')
                    <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Permission</a>
                    @endcan
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Guard</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permissions as $permission)
                                <tr>
                                    <td>{{ $permission->name }}</td>
                                    <td>{{ $permission->guard_name }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            @can('edit permissions')
                                                <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                            @endcan
                                            @can('delete permissions')
                                                <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" onsubmit="return confirm('Delete this permission?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center">No permissions found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $permissions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </section>
</div>
@endsection
