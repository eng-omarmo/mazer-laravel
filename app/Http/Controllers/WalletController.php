<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index()
    {
        $wallet = Wallet::main();

        return view('hrm.wallet', compact('wallet'));
    }

    public function deposit(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);
        $wallet = Wallet::main();
        $wallet->update(['balance' => $wallet->balance + (float) $validated['amount']]);

        return redirect()->route('hrm.wallet.index')->with('status', 'Wallet funded');
    }
}
