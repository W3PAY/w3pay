<?php
exit; // TODO Remove to use
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="files/icons/favicon.ico">
</head>
<body>
<?php
include_once(__DIR__ . '/w3payDefines.php');
// Set the right paths
if(!defined('_W3PAY_w3payFrontend_')){ define('_W3PAY_w3payFrontend_', '/w3pay/w3payFrontend'); }
if(!defined('_W3PAY_w3payBackend_')){ define('_W3PAY_w3payBackend_', __DIR__ . '/../w3payBackend'); }

// Include php class widget wW3pay.php
include_once(_W3PAY_w3payBackend_. '/widget/wW3pay.php');
// Set prices to receive tokens
$orderId = time(); // Please enter your order number
$payAmountInReceiveToken = 1; // Please enter a price for the order
$OrderData = [
    'orderId' => $orderId,
    'payAmounts' => [
        ['chainId' => 97, 'payAmountInReceiveToken' => $payAmountInReceiveToken], // Binance Smart Chain Mainnet - Testnet (BEP20)
        ['chainId' => 56, 'payAmountInReceiveToken' => $payAmountInReceiveToken], // Binance Smart Chain Mainnet (BEP20)
        ['chainId' => 137, 'payAmountInReceiveToken' => $payAmountInReceiveToken], // Polygon (MATIC)
        ['chainId' => 43114, 'payAmountInReceiveToken' => $payAmountInReceiveToken], // Avalanche C-Chain
        ['chainId' => 250, 'payAmountInReceiveToken' => $payAmountInReceiveToken], // Fantom Opera
    ],
];
$showPayment = \wW3pay::instance()->showPayment([
    'checkPaymentPageUrl'=>'/w3pay/w3payFrontend/checkPayment.php',
    'OrderData' => $OrderData,
]);
if(!empty($showPayment['head'])){ echo $showPayment['head']; } // Show js, css files
if(!empty($showPayment['html'])){ echo $showPayment['html']; } // Show html content
?>
</body>
</html>