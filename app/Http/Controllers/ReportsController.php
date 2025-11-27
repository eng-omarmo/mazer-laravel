<?php

namespace App\Http\Controllers;

use App\Models\EmployeeAdvance;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function advances(Request $request)
    {
        $query = EmployeeAdvance::query()->with('employee');
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        $advances = $query->orderByDesc('date')->paginate(20)->appends($request->query());
        $outstandingTotal = EmployeeAdvance::whereIn('status', ['approved'])->sum('remaining_balance');
        $fullyRepaid = EmployeeAdvance::where('status', 'paid')->count();
        $overdueCount = EmployeeAdvance::whereIn('status', ['approved'])->whereNotNull('next_due_date')->where('next_due_date', '<', now()->toDateString())->count();

        return view('hrm.reports-advances', compact('advances', 'outstandingTotal', 'fullyRepaid', 'overdueCount'));
    }
}
