<?php

namespace App\Listeners;

use App\Events\PaymentProcessedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandlePaymentConfirmationListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentProcessedEvent $event): void
    {        
        Log::info("Payment processed through {$event->gateway}", [
            'amount' => $event->amount,
            'currency' => $event->currencyId,
            'transaction_id' => $event->transactionId,
        ]);
    }
}
