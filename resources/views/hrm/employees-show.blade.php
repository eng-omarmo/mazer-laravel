@extends('layouts.master')
@section('title','Employee')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row align-items-center">
            <div class="col-12 col-md-6">
                <h3><i class="bi bi-person-badge me-2"></i>Employee Details</h3>
                <p class="text-muted">Overview & Profile Information</p>
            </div>
            <div class="col-12 col-md-6 text-md-end">
                <nav aria-label="breadcrumb" class="breadcrumb-header">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('hrm.employees.index') }}">
                                <i class="bi bi-people-fill me-1"></i>Employees
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <i class="bi bi-eye-fill me-1"></i>Show
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section mt-3">
        <div class="card shadow-sm">
            <div class="card-body">

                <h5 class="mb-4"><i class="bi bi-person-lines-fill me-2"></i>Personal Information</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong><i class="bi bi-person-fill"></i> Name:</strong>
                        <div>{{ $employee->first_name }} {{ $employee->last_name }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong><i class="bi bi-envelope-fill"></i> Email:</strong>
                        <div>{{ $employee->email }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong><i class="bi bi-building"></i> Department:</strong>
                        <div>
                            {{ optional($employee->department)->code }}
                            {{ optional($employee->department) ? '-' : '' }}
                            {{ optional($employee->department)->name }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <strong><i class="bi bi-briefcase-fill"></i> Position:</strong>
                        <div>{{ $employee->position }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong><i class="bi bi-cash-stack"></i> Salary:</strong>
                        <div>{{ $employee->salary !== null ? number_format($employee->salary,2) : '-' }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong><i class="bi bi-gift"></i> Commission:</strong>
                        <div>{{ $employee->bonus !== null ? number_format($employee->bonus,2) : '-' }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong><i class="bi bi-calendar-event-fill"></i> Hire Date:</strong>
                        <div>{{ $employee->hire_date }}</div>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-3"><i class="bi bi-person-lines-fill me-2"></i>Reference Person</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Full Name:</strong>
                        <div>{{ $employee->reference_full_name ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Phone Number:</strong>
                        <div>{{ $employee->reference_phone ?? '-' }}</div>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <strong>Identity Document Number:</strong>
                        <div>{{ $employee->identity_doc_number ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Identity Document File:</strong>
                        <div>
                            @php $idDoc = $employee->documents->firstWhere('type','identity'); @endphp
                            @if($idDoc)
                                <a href="{{ asset('storage/'.$idDoc->path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-earmark-person"></i> View Identity Document
                                </a>
                                <span class="badge bg-{{ $idDoc->status === 'approved' ? 'success' : ($idDoc->status === 'rejected' ? 'danger' : 'warning') }} ms-2">{{ ucfirst($idDoc->status) }}</span>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <strong>Fingerprint ID:</strong>
                        <div>{{ $employee->fingerprint_id ?? '-' }}</div>
                    </div>
                </div>

                <hr class="my-4">

                <h5><i class="bi bi-folder2-open me-2"></i>Documents</h5>
                <ul class="list-group mt-3">
                    @foreach($employee->documents as $doc)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-file-earmark-text me-1"></i>
                                {{ ucfirst($doc->type) }}
                                <span class="badge bg-{{
                                    $doc->status === 'approved' ? 'success' :
                                    ($doc->status === 'rejected' ? 'danger' : 'warning')
                                }} ms-2">
                                    {{ ucfirst($doc->status) }}
                                </span>
                            </span>

                            <a href="{{ asset('storage/'.$doc->path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-4 d-flex gap-2">
                    <a href="{{ route('hrm.employees.edit', $employee) }}" class="btn btn-primary">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>

                    <a href="{{ route('hrm.employees.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>

            </div>
        </div>
    </section>
</div>
@endsection
