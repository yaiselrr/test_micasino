<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Services\Payment\SuperWalletzGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class SuperWalletzGatewayTest extends TestCase
{
    public function test_process_payment_successfully()
    {
        $gateway = "super_walletz";
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
            'api.superwalletz.example/pay' => Http::response(['success' => true], 200),
        ]);

        

        $service = new SuperWalletzGateway();
        $response = $service->processPayment($transaction);

        $this->assertEquals('success', $response['status']);
    }

    public function test_webkook_successfully()
    {
        $status = "success";
        $randomNumber = rand(10000, 99999); 
        $transaction_id = 'trx_'.$randomNumber;

        $service = new SuperWalletzGateway();
        $response = $service->handleWebhook($transaction_id, $status);

        $this->assertEquals('success', $response['status']);
    }
}
