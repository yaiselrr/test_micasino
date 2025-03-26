<?php

return [
    'gateways' => [
        'easy_money' => [
            'base_url' => env('EASYMONEY_URL', 'http://localhost:3000'),
            'process_path' => '/process',
        ],
        'super_walletz' => [
            'base_url' => env('SUPERWALLETZ_URL', 'http://localhost:3003'),
            'pay_path' => '/pay',
            'callback_url' => env('APP_URL') . '/api/payment/webhook/super-walletz',
        ],
    ],
];