<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Jobs\CalculateRebate;

class WalletTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Queue::fake(); // Prevent actual job execution during tests
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_deposit_funds_and_calculate_rebate()
    {   
        // Queue::fake();
        $wallet = Wallet::create(['user_id' => 1, 'balance' => 0]);
        
        $response = $this->postJson("/wallets/{$wallet->id}/deposit", ['amount' => 100]);
        
        $response->assertStatus(200);
        
        $wallet->refresh();
        
        $this->assertEquals(101, $wallet->balance);
        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'deposit',
            'amount' => 100,
        ]);

        // Queue::assertPushed(CalculateRebate::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_concurrent_deposits_correctly()
    {
        $wallet = Wallet::create(['user_id' => 1, 'balance' => 0]);

        $responses = [
            $this->postJson("/wallets/{$wallet->id}/deposit", ['amount' => 100]),
            $this->postJson("/wallets/{$wallet->id}/deposit", ['amount' => 200]),
        ];

        foreach ($responses as $response) {
            $response->assertStatus(200);
        }

        $wallet->refresh();
        $this->assertEquals(303, $wallet->balance);

        $this->assertDatabaseCount('transactions', 4); // Ensure 2 deposit transactions are recorded
    }

    // #[\PHPUnit\Framework\Attributes\Test]
    // public function it_can_withdraw_funds_from_wallet()
    // {
    //     $wallet = Wallet::create(['user_id' => 1, 'balance' => 200]);

    //     $response = $this->postJson("/wallets/{$wallet->id}/withdraw", ['amount' => 50]);

    //     $response->assertStatus(200);
    //     $wallet->refresh();

    //     $this->assertEquals(150, $wallet->balance); // Balance after withdrawal
    //     $this->assertDatabaseHas('transactions', [
    //         'wallet_id' => $wallet->id,
    //         'type' => 'withdrawal',
    //         'amount' => 50,
    //     ]);
    // }
}
