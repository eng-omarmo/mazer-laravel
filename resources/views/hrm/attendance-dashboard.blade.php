@extends('layouts.master')
@section('title','Attendance Dashboard')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Attendance Dashboard</h3>
                <p class="text-subtitle text-muted">Quick insights</p>
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
        <div class="row">
            @php
                $today = now()->toDateString();
                $presentToday = \App\Models\AttendanceLog::where('date',$today)->where('status','present')->count();
                $absentToday = \App\Models\AttendanceLog::where('date',$today)->where('status','absent')->count();
                $lateToday = \App\Models\AttendanceLog::where('date',$today)->where('status','late')->count();
                $onLeaveToday = \App\Models\EmployeeLeave::where('start_date','<=',$today)->where('end_date','>=',$today)->where('status','approved')->count();
            @endphp
            <div class="col-md-3">
                <div class="card"><div class="card-body"><h6>Present Today</h6><h3>{{ $presentToday }}</h3></div></div>
            </div>
            <div class="col-md-3">
                <div class="card"><div class="card-body"><h6>Absent Today</h6><h3>{{ $absentToday }}</h3></div></div>
            </div>
            <div class="col-md-3">
                <div class="card"><div class="card-body"><h6>Late Today</h6><h3>{{ $lateToday }}</h3></div></div>
            </div>
            <div class="col-md-3">
                <div class="card"><div class="card-body"><h6>On Leave</h6><h3>{{ $onLeaveToday }}</h3></div></div>
            </div>
        </div>

        @php
            $start = now()->subDays(29)->startOfDay();
            $trend = [];
            for($i=0;$i<30;$i++){
                $d = $start->clone()->addDays($i)->toDateString();
                $trend[] = [
                    'date' => $d,
                    'present' => \App\Models\AttendanceLog::where('date',$d)->where('status','present')->count(),
                    'absent' => \App\Models\AttendanceLog::where('date',$d)->where('status','absent')->count(),
                    'late' => \App\Models\AttendanceLog::where('date',$d)->where('status','late')->count(),
                ];
            }
        @endphp
        <div class="card mt-3">
            <div class="card-body">
                <h6>Attendance Trend (Last 30 days)</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Date</th><th>Present</th><th>Absent</th><th>Late</th></tr></thead>
                        <tbody>
                            @foreach($trend as $t)
                                <tr>
                                    <td>{{ $t['date'] }}</td>
                                    <td>{{ $t['present'] }}</td>
                                    <td>{{ $t['absent'] }}</td>
                                    <td>{{ $t['late'] }}</td>
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