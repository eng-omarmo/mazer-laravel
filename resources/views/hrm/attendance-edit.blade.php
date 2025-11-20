@extends('layouts.master')
@section('title','Mark Attendance')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>{{ $log ? 'Edit Attendance' : 'Mark Attendance' }}</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('hrm.attendance.index') }}">Attendance</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $log ? 'Edit' : 'Create' }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <form method="post" action="{{ $log ? route('hrm.attendance.update', $log) : route('hrm.attendance.store') }}">
                    @csrf
                    @if($log) @method('patch') @endif
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employee</label>
                            <select name="employee_id" class="form-select">
                                @foreach($employees as $e)
                                    <option value="{{ $e->id }}" {{ old('employee_id', $log->employee_id ?? '') == $e->id ? 'selected' : '' }}>{{ $e->first_name }} {{ $e->last_name }} ({{ $e->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', $log->date ?? now()->toDateString()) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach(['present','absent','late','early_leave'] as $s)
                                    <option value="{{ $s }}" {{ old('status', $log->status ?? 'present') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $s)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Check-in</label>
                            <input type="time" name="check_in" class="form-control" value="{{ old('check_in', $log->check_in ?? '') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Check-out</label>
                            <input type="time" name="check_out" class="form-control" value="{{ old('check_out', $log->check_out ?? '') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Source</label>
                            <select name="source" class="form-select">
                                @foreach(['manual','device'] as $src)
                                    <option value="{{ $src }}" {{ old('source', $log->source ?? 'manual') === $src ? 'selected' : '' }}>{{ ucfirst($src) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="{{ route('hrm.attendance.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection