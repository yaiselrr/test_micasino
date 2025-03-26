<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\PaymentLog;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EasyMoneyGateway implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected string $processPath;

    public function __construct()
    {
        $this->baseUrl = config('payment.gateways.easy_money.base_url');
        $this->processPath = config('payment.gateways.easy_money.process_path');
    }

    public function processPayment(Transaction $transaction): Transaction
    {
        $transaction_update = Transaction::findOrFail($transaction['id']);

        if (fmod($transaction['amount'], 1) !== 0.00) {
            $transaction_update->update([
                'status' => 'failed',
                'metadata' => ['error' => 'EasyMoney cannot process decimal amounts']
            ]);
            
            PaymentLog::create([
                'transaction_id' => $transaction_update->id,
                'type' => 'response',
                'payload' => 'Decimal amounts not allowed'
            ]);

            throw new \InvalidArgumentException(
                "EasyMoney doesn't support decimal amounts. Use an integer value (e.g., 100 instead of 100.50)."
            );
        }

        $requestData = [
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
        ];

        // Log the request
        PaymentLog::create([
            'transaction_id' => $transaction_update->id,
            'type' => 'request',
            'payload' => json_encode($requestData, true)
        ]);

        try {
            $response = Http::post($this->baseUrl . $this->processPath, $requestData);            
            $responseData = $response->json();
            
            // Log the response
            PaymentLog::create([
                'transaction_id' => $transaction_update->id,
                'type' => 'response',
                'payload' => json_encode($responseData, true)
            ]);

            if ($response->successful()) {
                $transaction_update->update([
                    'status' => 'success',
                    'transaction_id' => $responseData['transaction_id'] ?? null
                ]);
            } else {
                $transaction_update->update([
                    'status' => 'failed',
                    'metadata' => ['error' => $responseData['message'] ?? 'Unknown error']
                ]);
            }
        } catch (\Exception $e) {
            Log::error("EasyMoney payment failed: " . $e->getMessage());

            PaymentLog::create([
                'transaction_id' => $transaction_update->id,
                'type' => 'response',
                'payload' => $e->getMessage()
            ]);

            $transaction_update->update([
                'status' => 'failed',
                'metadata' => ['error' => $e->getMessage()]
            ]);
        }

        return $transaction_update;
    }
}