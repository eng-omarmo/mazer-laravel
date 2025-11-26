<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentVerificationController extends Controller
{
    public function index()
    {
        $pendingDocuments = EmployeeDocument::with('employee')
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('hrm.verification', compact('pendingDocuments'));
    }

    public function approve(EmployeeDocument $document)
    {
        $document->update([
            'status' => 'approved',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('status', 'Document approved');
    }

    public function reject(Request $request, EmployeeDocument $document)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $document->update([
            'status' => 'rejected',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'rejection_reason' => $validated['reason'],
        ]);

        return back()->with('status', 'Document rejected');
    }
}
