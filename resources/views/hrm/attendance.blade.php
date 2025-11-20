@extends('layouts.master')
@section('title','Attendance')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Attendance</h3>
                <p class="text-subtitle text-muted">Daily logs</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Attendance</li>
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
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <div class="d-flex mb-3 align-items-end gap-2">
                    <a href="{{ route('hrm.attendance.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Mark Attendance</a>
                    <form class="row g-2" method="get" action="{{ route('hrm.attendance.index') }}">
                        <div class="col">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" value="{{ request('date') }}" class="form-control">
                        </div>
                        <div class="col">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">All</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                @foreach(['present','absent','late','early_leave'] as $s)
                                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $s)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-funnel"></i> Filter</button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Date</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->employee->first_name }} {{ $log->employee->last_name }}</td>
                                    <td>{{ optional($log->employee->department)->name }}</td>
                                    <td>{{ $log->date }}</td>
                                    <td>{{ $log->check_in ?? '-' }}</td>
                                    <td>{{ $log->check_out ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $log->status === 'present' ? 'success' : ($log->status === 'absent' ? 'secondary' : ($log->status === 'late' ? 'warning' : 'info')) }}">{{ ucfirst(str_replace('_',' ', $log->status)) }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('hrm.attendance.edit', $log) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil-square"></i> Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center">No logs</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $logs->links() }}
            </div>
        </div>
    </section>
</div>
@endsection