@extends('layouts.master')
@section('title','Employee Details')
@section('content')
<div class="page-heading">
    <h3>Employee Details</h3>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-2">{{ $employee->first_name }} {{ $employee->last_name }}</h5>
                        <p class="mb-1">{{ $employee->email }}</p>
                        <p class="mb-1">{{ $employee->phone }}</p>
                        <p class="mb-1">{{ $employee->department?->name }}</p>
                        <p class="mb-1">{{ ucfirst($employee->employment_type) }}</p>
                        <p class="mb-1">{{ ucfirst($employee->status) }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1">{{ $employee->address }}</p>
                        <p class="mb-1">{{ $employee->dob }}</p>
                        <p class="mb-1">{{ $employee->gender }}</p>
                        <p class="mb-1">{{ $employee->designation }}</p>
                        <p class="mb-1">{{ $employee->join_date }}</p>
                    </div>
                </div>
                <a href="{{ route('hrm.employees.index') }}" class="btn btn-secondary mt-3">Back</a>
            </div>
        </div>
    </section>
</div>
@endsection