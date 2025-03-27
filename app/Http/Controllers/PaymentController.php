<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Requests\WebhookRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Currency;
use App\Models\Transaction;
use App\Services\Payment\PaymentService;
use App\Services\Payment\SuperWalletzGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService)
    {
    }
    
    public function process(ProcessPaymentRequest $request): JsonResponse
    {
        $gateway = $request->input('gateway');
        $amount = $request->input('amount');
        $currency = $request->input('currency');

        if ($amount <= 0) {
            return response()->json([
                'message' => 'The specified amount must be greater than zero.'
            ], 400);
        }

        if (!strlen($currency) === 3) {
            return response()->json([
                'message' => 'The specified currency is not correct.'
            ], 400);
        }

        if (fmod($amount, 1) !== 0.00 && $gateway === 'easy_money') {
            return response()->json([
                'message' => 'EasyMoney doesnt support decimal amounts. Use an integer value (e.g., 100 instead of 100.50).'
            ], 400);                
        }

        $currency_find = Currency::where('name', $currency)->first();

        if ($currency_find == null) {
            return response()->json([
                'message' => 'The specified currency is not correct.'
            ], 400);
        }

        // Create transaction
        $transaction = Transaction::create([
            'reference' => uniqid('tx_'),
            'amount' => $amount,
            'currency_id' => $currency_find->id,
            'payment_system' => $gateway,
            'status' => 'pending',
        ]);

        try {
            $result = $this->paymentService->process($transaction, $gateway);

            if ($result) {
                return response()->json([
                    'message' => 'Payment processed successfully',
                    'data' => new TransactionResource($result)
                ]);
            }

            return response()->json([
                'error' => $result['error'],
            ], $result['status'] ?? 400);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred',
            ], 500);
        }
    }

    public function handleSuperWalletzWebhook(WebhookRequest $request)
    {
        try {
            $transaction_id = $request->input('transaction_id');
            $status = $request->input('status');
            
            Log::info("SuperWalletz webhook received", [
                'transaction_id' => $transaction_id,
                'status' => $status,
            ]);

            $service = new SuperWalletzGateway();
            $webhook = $service->handleWebhook($transaction_id, $status);
            
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
