<?php
exit; // TODO Remove to use
include_once(__DIR__ . '/w3payDefines.php');
// Set the right paths
if(!defined('_W3PAY_w3payFrontend_')){ define('_W3PAY_w3payFrontend_', '/w3pay/w3payFrontend'); }
if(!defined('_W3PAY_w3payBackend_')){ define('_W3PAY_w3payBackend_', __DIR__ . '/../w3payBackend'); }

include_once(_W3PAY_w3payBackend_. '/widget/wW3pay.php');
\wW3pay::instance()->showLoad(['checkAuthRequired'=>true]);
exit;


