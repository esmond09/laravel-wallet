<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalculateRebate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $walletId;
    protected $depositAmount;
    /**
     * Create a new job instance.
     * @return void
     */
    public function __construct($walletId, $depositAmount)
    {   
        $this->walletId = $walletId;
        $this->depositAmount = $depositAmount;

        Log::info('CalculateRebate job created', [
            'wallet_id' => $walletId,
            'amount' => $depositAmount,
        ]);
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        $wallet = Wallet::find($this->walletId);
        if ($wallet) {
            $rebate = $this->depositAmount * 0.01; // 1% rebate
            $wallet->balance += $rebate;
            $wallet->save();

            // Record the rebate transaction
            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'rebate',
                'amount' => $rebate,
            ]);
}
    }
}