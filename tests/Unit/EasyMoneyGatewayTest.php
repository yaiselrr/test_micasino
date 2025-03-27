<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Services\Payment\EasyMoneyGateway;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EasyMoneyGatewayTest extends TestCase
{
    public function test_process_payment_successfully()
    {
        $gateway = "easy_money";
        $amount = 100;
        $currency = 1;

        // Create transaction
        $transaction = Transaction::create([
            'reference' => uniqid('tx_'),
            'amount' => $amount,
            'currency_id' => $currency,
            'payment_system' => $gateway,
            'status' => 'pending',
        ]);

        Http::fake([
            'api.easymoney.example/process' => Http::response(['success' => true], 200),
        ]);

        

        $service = new EasyMoneyGateway();
        $response = $service->processPayment($transaction);

        $this->assertEquals('success', $response['status']);
    }

    public function test_process_payment_fails()
    {
        $gateway = "easy_money";
        $amount = 100.5;
        $currency = 1;

        // Create transaction
        $transaction = Transaction::create([
            'reference' => uniqid('tx_'),
            'amount' => $amount,
            'currency_id' => $currency,
            'payment_system' => $gateway,
            'status' => 'pending',
        ]);

        Http::fake([
            'api.easymoney.example/process' => Http::response(['success' => true], 200),
        ]);        

        $service = new EasyMoneyGateway();
        $response = $service->processPayment($transaction);

        $this->assertEquals('failed', $response['status']);
    }
}
