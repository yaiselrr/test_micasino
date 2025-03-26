<?php

namespace App\Providers;

use App\Events\PaymentProcessedEvent;
use App\Listeners\HandlePaymentConfirmationListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PaymentProcessedEvent::class => [
            HandlePaymentConfirmationListener::class,
        ],
    ];
}