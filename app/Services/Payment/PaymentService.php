<?php

namespace App\Services\Payment;

use App\Events\PaymentProcessedEvent;
use App\Models\Transaction;
use InvalidArgumentException;

class PaymentService
{
    protected array $gateways;

    public function __construct()
    {
        $this->gateways = [
            'easy_money' => new EasyMoneyGateway(config('payment.gateways.easy_money')),
            'super_walletz' => new SuperWalletzGateway(config('payment.gateways.super_walletz')),
        ];
    }

    public function process(Transaction $transaction, $gateway): Transaction
    {
        if (!array_key_exists($gateway, $this->gateways)) {
            throw new InvalidArgumentException("Payment gateway {$gateway} not supported");
        }

        $result = $this->gateways[$gateway]->processPayment($transaction);

        if ($result['id']) {
            event(new PaymentProcessedEvent(
                $gateway,
                $result->amount,
                $result->currency_id,
                $result->transaction_id ?? null
            ));
        }

        return $result;
    }
}