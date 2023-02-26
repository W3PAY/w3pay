<?php
include_once(__DIR__ . '/w3payDefines.php'); // TODO Delete line to use
// TODO Move the content of the file outside of the w3pay folder.
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="files/icons/favicon.ico">
</head>
<body>
<?php
// Set the right paths
if(!defined('_W3PAY_w3payFrontend_')){ define('_W3PAY_w3payFrontend_', '/w3pay/w3payFrontend'); }
if(!defined('_W3PAY_w3payBackend_')){ define('_W3PAY_w3payBackend_', __DIR__ . '/../w3payBackend'); }

// Include php class widget wW3pay.php
include_once(_W3PAY_w3payBackend_. '/widget/wW3pay.php');
$showCheckPayment = \wW3pay::instance()->showCheckPayment([
    //'htmlSuccess' => '<a class="checkPaymentBtn" href="/">Home</a>',
    //'htmlError' => '<a class="checkPaymentBtn" href="/">Home</a>',
]);
if(!empty($showCheckPayment['CheckPaymentData']['showSuccess'])){
    $orderId = $showCheckPayment['CheckPaymentData']['checkSign']['checkSign']['orderId'];
    // TODO The administrator can mark $orderId the successful payment in the database.
} else {
    if(!empty($showCheckPayment['CheckPaymentData']['checkSign']['typeError']) && $showCheckPayment['CheckPaymentData']['checkSign']['typeError']=='SignaturFalse'){
        $orderId = $showCheckPayment['CheckPaymentData']['checkSign']['checkSign']['orderId'];
        // TODO The administrator can mark $orderId the failed payment in the database.
    }
}
if(!empty($showCheckPayment['head'])){ echo $showCheckPayment['head']; } // Show js, css files
if(!empty($showCheckPayment['html'])){ echo $showCheckPayment['html']; } // Show html content
?>
</body>
</html>