<?php

namespace App\Contracts;

use App\Models\Transaction;

interface PaymentGatewayInterface
{
    public function processPayment(Transaction $transaction): Transaction;
    
}