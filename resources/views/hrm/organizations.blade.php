@extends('layouts.master')
@section('title','Organizations')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Organizations</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Organizations</li>
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
                    <a href="{{ route('hrm.organizations.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Organization</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($organizations as $o)
                            <tr>
                                <td>{{ $o->id }}</td>
                                <td>{{ $o->name }}</td>
                                <td class="d-flex gap-1">
                                    <a href="{{ route('hrm.organizations.edit', $o) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
                                    <form method="post" action="{{ route('hrm.organizations.destroy', $o) }}" onsubmit="return confirm('Delete organization?')">
                                        @csrf
                                        @method('delete')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $organizations->links() }}
            </div>
        </div>
    </section>
</div>
@endsection
