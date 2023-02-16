<?php
/**
 * W3PAY - Web3 Crypto Payments
 * Website: https://w3pay.dev
 * GitHub Website: https://w3pay.github.io/
 * GitHub: https://github.com/w3pay
 * Copyright (c)
 */

class sDefines
{

    protected static $instance;

    /**
     * @return sDefines
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * checkDefines
     */
    public function checkDefines(){
        if(!defined('_W3PAY_w3payFrontend_')){
            echo 'w3payDefines error: CONSTANT _W3PAY_w3payFrontend_ is empty';
            exit;
        }
        if(!defined('_W3PAY_w3payBackend_')){
            echo 'w3payDefines error: CONSTANT _W3PAY_w3payBackend_ is empty';
            exit;
        }
        // check defines
        if(empty($_SERVER['DOCUMENT_ROOT'])){
            echo 'w3payDefines error: DOCUMENT_ROOT is empty';
            exit;
        }
        $W3PAY_checkFile = sDefines::instance()->getPaths()['w3payFrontendPath'].'/files/languages/en.json';
        if(!file_exists($W3PAY_checkFile) && !is_file($W3PAY_checkFile)){
            echo 'w3payDefines error: File '.$W3PAY_checkFile.' not found.';
            exit;
        }
        $W3PAY_checkFile = _W3PAY_w3payBackend_.'/widget/wW3pay.php';
        if(!file_exists($W3PAY_checkFile) && !is_file($W3PAY_checkFile)){
            echo 'w3payDefines error: File '.$W3PAY_checkFile.' not found.';
            exit;
        }
        if(!defined('_W3PAY_Cache_version_')){ define('_W3PAY_Cache_version_', '1'); }
    }

    /**
     * @return array
     */
    public function getPaths(){
        $Paths = [
            'w3payFrontend' => _W3PAY_w3payFrontend_,
            'w3payBackend' => _W3PAY_w3payBackend_,
            'w3payFrontendPath' => $_SERVER['DOCUMENT_ROOT']._W3PAY_w3payFrontend_,
        ];
        return $Paths;
    }

}