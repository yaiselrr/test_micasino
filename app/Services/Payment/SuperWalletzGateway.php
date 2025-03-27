<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\PaymentLog;
use App\Models\Transaction;
use App\Models\Webhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SuperWalletzGateway implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected string $payPath;
    protected string $callbackUrl;

    public function __construct()
    {
        $this->baseUrl = config('payment.gateways.super_walletz.base_url');
        $this->payPath = config('payment.gateways.super_walletz.pay_path');
        $this->callbackUrl = config('payment.gateways.super_walletz.callback_url');
    }

    public function processPayment(Transaction $transaction): Transaction
    {
        $requestData = [
            'amount' => $transaction['amount'],
            'currency' => $transaction['currency'],
            'callback_url' => route('payment.webhook.super_walletz')
        ];
        
        // Log the request
        PaymentLog::create([
            'transaction_id' => $transaction['id'],
            'type' => 'request',
            'payload' => $requestData
        ]);

        try {
            $response = Http::post("{$this->baseUrl}/pay", $requestData);            
            $responseData = $response->json();

            // Log the response
            PaymentLog::create([
                'transaction_id' => $transaction['id'],
                'type' => 'response',
                'payload' => $requestData
            ]);

            if ($response->successful()) {
                $transaction->update([
                    'status' => 'success',
                    'transaction_id' => $responseData['transaction_id'] ?? null
                ]);
            } else {
                $transaction->update([
                    'status' => 'failed',
                    'metadata' => ['error' => $responseData['message'] ?? 'Unknown error']
                ]);
            }
        } catch (\Exception $e) {
            Log::error("SuperWalletz payment failed: " . $e->getMessage());

            PaymentLog::create([
                'transaction_id' => $transaction['id'],
                'type' => 'response',
                'payload' => ['error' => $e->getMessage()]
            ]);
            
            $transaction->update([
                'status' => 'failed',
                'metadata' => ['error' => $e->getMessage()]
            ]);
        }

        return $transaction;
    }

    public function handleWebhook($transaction_id, $status)
    {
        Log::info("SuperWalletz webhook save", [
            'transaction_id' => $transaction_id,
            'status' => $status,
        ]);

        $webhook = Webhook::where('transaction_id', $transaction_id)->first();

        if ($webhook == null) {
            $webhookCreate = Webhook::create([
                'transaction_id' => $transaction_id,
                'status' => $status
            ]);

            return $webhookCreate;
        }

        return null;


        // $transaction = Transaction::where('transaction_id', $transaction_id)->first();

        // Log the webhook
        // PaymentLog::create([
        //     'transaction_id' => $transaction->id,
        //     'type' => 'webhook',
        //     'payload' => json_encode(['transaction_id' => $transaction_id, 'status' => $status], true)
        // ]);

        // if ($status === 'success') {
        //     $transaction->update(['status' => 'success']);
        // } else {
        //     $transaction->update([
        //         'status' => 'failed',
        //         'metadata' => []
        //     ]);
        // }
        
    }
}