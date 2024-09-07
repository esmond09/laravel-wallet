<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;

Route::get('/', function () {
    return view('welcome');
});

// Deposit funds into a wallet
Route::post('/wallets/{wallet}/deposit', [WalletController::class, 'deposit']);

// Withdraw funds from a wallet
Route::post('/wallets/{wallet}/withdraw', [WalletController::class, 'withdraw']);

// Get wallet balance
Route::get('/wallets/{wallet}/balance', [WalletController::class, 'balance']);

// Get transaction history
Route::get('/wallets/{wallet}/transactions', [WalletController::class, 'transactions']);