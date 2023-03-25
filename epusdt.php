<?php

if (!defined('WHMCS')) die('This file cannot be accessed directly');

if (!class_exists('Epusdt')) {
    require __DIR__ . '/epusdt/lib/epusdt.php';
}


function epusdt_MetaData()
{
    return array(
        'DisplayName' => 'Accept payments by USDT',
    );
}

function epusdt_config()
{
    return [
        'FriendlyName' => [
            'Type' => 'System',
            'Value' => 'EPUSDT',
        ],
        'endpoint' => [
            'FriendlyName' => 'Endpoint',
            'Type' => 'text',
            'Size' => 256,
            'Default' => '',
            'Description' => 'EPUSDT API endpoint',
        ],
        'secret' => [
            'FriendlyName' => 'Token',
            'Type' => 'text',
            'Size' => 256,
            'Default' => '',
            'Description' => 'The EPUSDT token',
        ]
    ];
}

function epusdt_link(array $parameters)
{
    $epusdt = new Epusdt($parameters['endpoint'], $parameters['secret']);
    $payload = [
        'order_id' => (string)$parameters['invoiceid'],
        'amount' => (float)$parameters['amount'],
        'notify_url' => rtrim($parameters['systemurl'], '/') . '/modules/gateways/callback/epusdt_notify.php',
        'redirect_url' => rtrim($parameters['systemurl'], '/') . '/modules/gateways/callback/epusdt_return.php?order_id=' . $parameters['invoiceid'],
    ];

    try {
        $redirectURL = $epusdt->redirectURL($payload);
    } catch (Exception $e) {
        return "支付接口错误: " . $e->getMessage();
    }

    return <<<HTML
        <script type="text/javascript">window.location.href="{$redirectURL}";</script>
    HTML;
}
