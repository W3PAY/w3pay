<?php
include_once(__DIR__ . '/w3payDefines.php'); // TODO Delete line to use
// TODO Move the content of the file outside of the w3pay folder.

// Set the right paths
if(!defined('_W3PAY_w3payFrontend_')){ define('_W3PAY_w3payFrontend_', '/w3pay/w3payFrontend'); }
if(!defined('_W3PAY_w3payBackend_')){ define('_W3PAY_w3payBackend_', __DIR__ . '/../w3payBackend'); }

include_once(_W3PAY_w3payBackend_. '/widget/wW3pay.php');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="files/icons/favicon.ico">
</head>
<body>
<?php
$Transactions = \wW3pay::instance()->showTransactions([
    'checkAuthRequired'=>true,
    'sendurl'=>_W3PAY_w3payFrontend_.'/load.php',
    'checkPaymentPageUrl'=>'/w3pay/w3payFrontend/checkPayment.php',
]);
if(!empty($Transactions['head'])){ echo $Transactions['head']; } // Show js, css files
if(!empty($Transactions['html'])){ echo $Transactions['html']; } // Show html content
?>
</body>
</html>


