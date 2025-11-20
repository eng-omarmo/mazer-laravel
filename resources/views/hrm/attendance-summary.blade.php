@extends('layouts.master')
@section('title','Attendance Summary')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Monthly Summary</h3>
                <p class="text-subtitle text-muted">Report foundation</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Summary</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <form class="row g-2 mb-3" method="get" action="{{ route('hrm.attendance.summary') }}">
                    <div class="col-md-3">
                        <label class="form-label">Year</label>
                        <input type="number" name="year" value="{{ $year }}" class="form-control" min="2000" max="2100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Month</label>
                        <input type="number" name="month" value="{{ $month }}" class="form-control" min="1" max="12">
                    </div>
                    <div class="col-auto align-self-end">
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-funnel"></i> Filter</button>
                        <a class="btn btn-outline-success" href="{{ route('hrm.attendance.export.csv', ['year'=>$year,'month'=>$month]) }}"><i class="bi bi-file-earmark-spreadsheet"></i> Export CSV</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Late</th>
                                <th>Early Leave</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $row)
                                <tr>
                                    <td>{{ $row['e']->first_name }} {{ $row['e']->last_name }}</td>
                                    <td>{{ optional($row['e']->department)->name }}</td>
                                    <td>{{ $row['present'] }}</td>
                                    <td>{{ $row['absent'] }}</td>
                                    <td>{{ $row['late'] }}</td>
                                    <td>{{ $row['early'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection