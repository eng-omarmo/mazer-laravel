@extends('layouts.master')
@section('title','Department Details')
@section('content')
<div class="page-heading">
    <h3>Department Details</h3>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">{{ $department->name }}</h5>
                <p>{{ $department->description }}</p>
                <a href="{{ route('hrm.departments.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </section>
</div>
@endsection