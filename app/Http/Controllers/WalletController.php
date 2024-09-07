<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Jobs\CalculateRebate;

class WalletController extends Controller
{
    public function deposit(Request $request, $walletId)
    {
        $amount = $request->input('amount');

        DB::transaction(function () use ($walletId, $amount) {
            // Find the wallet and lock the row for the transaction
            $wallet = Wallet::lockForUpdate()->findOrFail($walletId);

            // Update the wallet balance
            $wallet->balance += $amount;
            $wallet->save();

            // Record the deposit transaction
            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $amount,
            ]);

            // Dispatch the rebate calculation job
            CalculateRebate::dispatch($wallet->id, $amount);
        });

        return response()->json(['message' => 'Deposit successful'], 200);

    }

    public function withdraw(Request $request, $walletId)
    {
        $amount = $request->input('amount');

        DB::transaction(function () use ($walletId, $amount) {
            // Find the wallet and lock the row for the transaction
            $wallet = Wallet::lockForUpdate()->find($walletId);

            // Ensure the wallet has sufficient balance
            if ($wallet->balance < $amount) {
                return response()->json(['message' => 'Insufficient balance'], 400);
            }

            // Update the wallet balance
            $wallet->balance -= $amount;
            $wallet->save();

            // Record the withdrawal transaction
            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'withdrawal',
                'amount' => $amount,
            ]);
        });

        return response()->json(['message' => 'Withdrawal successful'], 200);
    }

    public function showBalance(Wallet $wallet)
    {
        return response()->json(['balance' => $wallet->balance], 200);
    }

    public function showTransactions(Wallet $wallet)
    {
        $transactions = $wallet->transactions;
        return response()->json($transactions, 200);
    }

}
