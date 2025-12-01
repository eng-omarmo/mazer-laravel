@extends('layouts.master')
@section('title','Suppliers')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Suppliers</h3>
                <p class="text-subtitle text-muted">Manage supplier registrations</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Suppliers</li>
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
                    <a href="{{ route('hrm.suppliers.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Supplier</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Contact Person</th>
                                <th>Phone</th>
                                <th>Account</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suppliers as $s)
                                <tr>
                                    <td>{{ $s->name }}</td>
                                    <td>{{ $s->contact_person }}</td>
                                    <td>{{ $s->phone }}</td>
                                    <td>{{ $s->account }}</td>
                                    <td>{{ $s->address }}</td>
                                    <td><span class="badge bg-{{ $s->status==='active'?'success':'secondary' }}">{{ ucfirst($s->status) }}</span></td>
                                    <td>{{ \Illuminate\Support\Str::limit($s->notes, 50) }}</td>
                                    <td class="d-flex gap-2">
                                        <a href="{{ route('hrm.suppliers.edit', $s) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                                        <form method="post" action="{{ route('hrm.suppliers.destroy', $s) }}" onsubmit="return confirm('Delete this supplier?')">
                                            @csrf
                                            @method('delete')
                                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No suppliers</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $suppliers->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
