<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Services\MerchantPayService;
class WalletController extends Controller
{

    public function __construct(private MerchantPayService $merchantPayService)
    {
         $this->merchantPayService = $merchantPayService;
    }
    public function index()
    {
        $wallets = $this->merchantPayService->WalletsInfo();

        return view('hrm.wallet', compact('wallets'));
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
