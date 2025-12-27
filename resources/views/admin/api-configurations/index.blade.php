@extends('layouts.master')
@section('title', 'API Configurations')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>API Configurations</h3>
                <p class="text-subtitle text-muted">Manage external API tokens</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">API Configurations</li>
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
                    @can('create api configs')
                    <a href="{{ route('admin.api-configurations.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Configuration</a>
                    @endcan
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Token</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($configs as $config)
                                <tr>
                                    <td>{{ $config->id }}</td>
                                    <td>
                                        <code>{{ \Illuminate\Support\Str::limit($config->token, 8, '…') }}</code>
                                    </td>
                                    <td>{{ $config->created_at?->format('Y-m-d') }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            @can('edit api configs')
                                            <a href="{{ route('admin.api-configurations.edit', $config) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                            @endcan
                                            @can('delete api configs')
                                            <form action="{{ route('admin.api-configurations.destroy', $config) }}" method="POST" onsubmit="return confirm('Delete this configuration?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">No configurations found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $configs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </section>
@endsection
