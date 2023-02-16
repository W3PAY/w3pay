<?php
exit; // TODO Remove to use
include_once(__DIR__ . '/w3payDefines.php');
// Set the right paths
if(!defined('_W3PAY_w3payFrontend_')){ define('_W3PAY_w3payFrontend_', '/w3pay/w3payFrontend'); }
if(!defined('_W3PAY_w3payBackend_')){ define('_W3PAY_w3payBackend_', __DIR__ . '/../w3payBackend'); }

include_once(_W3PAY_w3payBackend_. '/widget/wW3pay.php');
//wW3pay::instance()->showSaveSettings();
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
$FormSettings = \wW3pay::instance()->showFormSettings(['checkAuthRequired'=>true, 'cms'=> 'none', 'sendurl'=>_W3PAY_w3payFrontend_.'/load.php']);
if(!empty($FormSettings['head'])){ echo $FormSettings['head']; } // Show js, css files
if(!empty($FormSettings['html'])){ echo $FormSettings['html']; } // Show html content
?>
</body>
</html>


