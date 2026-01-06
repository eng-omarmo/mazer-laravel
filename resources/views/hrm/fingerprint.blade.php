@extends('layouts.master')
@section('title','Fingerprint Capture')
@section('content')
<div class="page-heading">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Fingerprint Capture</h3>
                <p class="text-subtitle text-muted">Register employee biometrics</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Fingerprint</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <form action="{{ route('hrm.fingerprint.capture') }}" method="POST">
                        @csrf

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="employee_id">Select Employee</label>
                            <select name="employee_id" class="form-control" id="employee_id">
                                <option value="">-- Select Employee --</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Capture Fingerprint</button>
                    </div>
                    <div class="col-md-6">
                        <div id="status" class="mt-4"></div>
                    </div>
                               </form>
                </div>
            </div>
        </div>
    </section>
</div>


@endsection
