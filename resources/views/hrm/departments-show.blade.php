@extends('layouts.master')
@section('title','Department')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Department Details</h3>
                <p class="text-subtitle text-muted">Overview</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('hrm.departments.index') }}">Departments</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Show</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3"><strong>Code:</strong> {{ $department->code }}</div>
                    <div class="col-md-6 mb-3"><strong>Name:</strong> {{ $department->name }}</div>
                    <div class="col-md-6 mb-3"><strong>Head:</strong> {{ optional($department->head)->first_name }} {{ optional($department->head)->last_name }}</div>
                </div>
                <a href="{{ route('hrm.departments.edit', $department) }}" class="btn btn-outline-primary"><i class="bi bi-pencil-square"></i> Edit</a>
                <a href="{{ route('hrm.departments.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
            </div>
        </div>
    </section>
</div>
@endsection