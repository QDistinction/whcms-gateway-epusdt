<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

$gatewayConfig = getGatewayVariables('epusdt');
$payload = array_merge($_GET, $_POST);

header('Location: ' . rtrim($gatewayConfig['systemurl'], '/') . '/viewinvoice.php?id=' . $payload['order_id']);

