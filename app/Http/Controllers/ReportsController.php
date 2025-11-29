<?php

namespace App\Http\Controllers;

use App\Models\EmployeeAdvance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function advances(Request $request)
    {
        $query = EmployeeAdvance::query()->with('employee');
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', (int) $request->input('employee_id'));
        }
        $advances = $query->orderByDesc('date')->paginate(20)->appends($request->query());
        if ($request->filled('employee_id')) {
            $outstandingTotal = EmployeeAdvance::whereIn('status', ['approved'])
                ->where('employee_id', (int) $request->input('employee_id'))
                ->sum('remaining_balance');
        } else {
            $outstandingTotal = EmployeeAdvance::whereIn('status', ['approved'])->sum('remaining_balance');
        }
        $fullyRepaid = EmployeeAdvance::where('status', 'paid')->count();
        $overdueCount = EmployeeAdvance::whereIn('status', ['approved'])->whereNotNull('next_due_date')->where('next_due_date', '<', now()->toDateString())->count();
        $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();

        return view('hrm.reports-advances', compact('advances', 'outstandingTotal', 'fullyRepaid', 'overdueCount', 'employees'));
    }

    public function advancesCsv(Request $request): StreamedResponse
    {
        $query = EmployeeAdvance::query()->with('employee');
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', (int) $request->input('employee_id'));
        }
        $rows = $query->orderByDesc('date')->get();

        $response = new StreamedResponse(function () use ($rows, $request) {
            $out = fopen('php://output', 'w');
            $title = 'Employee Advances';
            if ($request->filled('employee_id') && $rows->count()>0) {
                $emp = optional($rows->first()->employee);
                fputcsv($out, ["Employee", $emp->first_name.' '.$emp->last_name, $emp->email ?? '']);
            }
            fputcsv($out, ['Date', 'Employee', 'Original Amount', 'Remaining', 'Next Due', 'Status']);
            $totalRemaining = 0.0;
            foreach ($rows as $a) {
                $remaining = (float) ($a->remaining_balance ?? $a->amount);
                $totalRemaining += $remaining;
                fputcsv($out, [
                    $a->date,
                    optional($a->employee)->first_name.' '.optional($a->employee)->last_name,
                    number_format($a->amount, 2),
                    number_format($remaining, 2),
                    $a->next_due_date ?: '-',
                    $a->status,
                ]);
            }
            fputcsv($out, []);
            fputcsv($out, ['Total Remaining', number_format($totalRemaining, 2)]);
            fclose($out);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $filename = $request->filled('employee_id') ? 'advances_employee_'.$request->input('employee_id').'.csv' : 'advances.csv';
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

        return $response;
    }
}
