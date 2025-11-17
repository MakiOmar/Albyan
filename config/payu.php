<?php

// NOTE: tzsk/payu package has been temporarily removed for Laravel 11 compatibility
// This config is disabled until a Laravel 11 compatible version is available
// See LARAVEL_11_INCOMPATIBLE_PACKAGES.md for details

/*
use Tzsk\Payu\Gateway\Gateway;
use Tzsk\Payu\Gateway\PayuBiz;
use Tzsk\Payu\Gateway\PayuMoney;
use Tzsk\Payu\Models\PayuTransaction;
*/

return [
    'default' => env('PAYU_DEFAULT_GATEWAY', 'biz'),

    'gateways' => [
        // PayuMoney and PayuBiz classes removed - package not compatible with Laravel 11
        // 'money' => new PayuMoney([
        //     'mode' => env('PAYU_MONEY_MODE', Gateway::TEST_MODE),
        //     'key' => env('PAYU_MONEY_KEY', 'mji6olvE'),
        //     'salt' => env('PAYU_MONEY_SALT', 'So86G6y4SP'),
        //     'auth' => env('PAYU_MONEY_AUTH'),
        // ]),

        // 'biz' => new PayuBiz([
        //     'mode' => env('PAYU_BIZ_MODE', Gateway::TEST_MODE),
        //     'key' => env('PAYU_BIZ_KEY', 'gtKFFx'),
        //     'salt' => env('PAYU_BIZ_SALT', 'eCwWELxi'),
        // ]),
    ],

    'verify' => [
        // PayuTransaction::STATUS_PENDING,
    ],
];
