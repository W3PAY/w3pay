<?php
/**
 * W3PAY - Web3 Crypto Payments
 * Website: https://w3pay.dev
 * GitHub Website: https://w3pay.github.io/
 * GitHub: https://github.com/w3pay
 * Copyright (c)
 */

include_once(__DIR__.'/../services/sUpdate.php');
include_once(__DIR__.'/../services/sDefines.php');
include_once(__DIR__.'/../services/sLanguage.php');
include_once(__DIR__.'/../services/sTransactions.php');
include_once(__DIR__.'/../services/sAssistant.php');

class wW3pay
{

    /**
     * wW3pay constructor.
     */
    function __construct() {
        sDefines::instance()->checkDefines();
    }

    protected static $instance;

    /**
     * @return wW3pay
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @param $data
     * @return array
     */

    public function showPayment($data){

        if(empty($data['OrderData']['orderId'])){
            return ['error' => 1, 'html'=>'orderId is empty'];
        }
        if(empty($data['OrderData']['payAmounts'])){
            return ['error' => 1, 'html'=>'payAmounts is empty'];
        }
        if(empty($data['FolderBackend'])){
            $data['FolderBackend'] = __DIR__.'/../../w3payBackend';
        }
        if(!file_exists($data['FolderBackend'])){
            return ['error' => 1, 'html'=>'FolderBackend not found'];
        }
        if(empty($data['FolderFrontendUrl'])){
            $data['FolderFrontendUrl'] = _W3PAY_w3payFrontend_;
        }
        if(empty($data['checkPaymentPageUrl'])){
            $data['checkPaymentPageUrl'] = '/w3pay/w3payFrontend/checkPayment.php';
        }
        $checkSettings = $this->checkSettings($data['FolderFrontendUrl']);
        if(!empty($checkSettings['error'])){
            return $checkSettings;
        }

        $head = '<meta name="viewport" content="width=device-width, initial-scale=1">';
        $head .= '<script type="text/javascript" src="'.$data['FolderFrontendUrl'].'/files/js/web3.min.js?cache='._W3PAY_Cache_version_.'"></script>';
        $head .= '<script type="text/javascript" src="'.$data['FolderFrontendUrl'].'/files/js/w3pay.min.js?cache='._W3PAY_Cache_version_.'"></script>';
        $head .= '<link href="'.$data['FolderFrontendUrl'].'/files/css/w3pay.css?cache='._W3PAY_Cache_version_.'" rel="stylesheet">';

        return ['error' => 0, 'head'=>$head, 'html'=>$this->getTemplateHtml(__DIR__.'/templates/payment.php', $data)];
    }

    /**
     * @param array $data
     * @return array
     */
    public function showCheckPayment($data=[]){

        $data['LanguageBlock'] = sLanguage::instance()->getLanguageBlock();

        if(empty($data['FolderBackend'])){
            $data['FolderBackend'] = __DIR__.'/../../w3payBackend';
        }
        if(!file_exists($data['FolderBackend'])){
            return ['error' => 1, 'html'=>'FolderBackend not found'];
        }
        if(empty($data['FolderFrontendUrl'])){
            $data['FolderFrontendUrl'] = _W3PAY_w3payFrontend_;
        }
        $checkSettings = $this->checkSettings($data['FolderFrontendUrl']);
        if(!empty($checkSettings['error'])){
            return $checkSettings;
        }

        $data['chainid'] = (isset($_GET['chainid']))?$_GET['chainid']:'';
        $data['tx'] = (isset($_GET['tx']))?$_GET['tx']:'';

        $data['checkPaymentText'] = '';
        if(empty($data['chainid'])){ $data['checkPaymentText'] .= 'Chain id is empty; '; }
        if(empty($data['tx'])){ $data['checkPaymentText'] .= 'TX is empty; '; }

        $data['showForm'] = false;
        $data['showSuccess'] = false;
        $data['showError'] = false;
        include_once($data['FolderBackend'] . '/services/sW3pay.php');
        if(!empty($data['chainid']) && !empty($data['tx'])){
            $data['checkSign'] = sW3pay::instance()->checkSign($data['chainid'], $data['tx']);
            if(empty($data['checkSign']['error']) && isset($data['checkSign']['error'])){
                // The signature is true. The details of the contract have been verified.
                // TODO The administrator can mark the successful payment in the database.
                $data['showSuccess'] = true;
            } else {
                // The signature is false. The data of the contract with the data on the site do not match.
                $data['showError'] = true;
                if(!empty($data['checkSign']['typeError']) && $data['checkSign']['typeError']=='TransactionNotFound'){
                    $data['showForm'] = true;
                }
            }
        } else {
            $data['showForm'] = true;
        }
        if(!empty($data['showForm'])){
            $data['PaymentSettings'] = sW3pay::instance()->getPaymentSettings();
        }

        $head = '<meta name="viewport" content="width=device-width, initial-scale=1">';
        $head .= '<link href="'.$data['FolderFrontendUrl'].'/files/css/w3pay.css?cache='._W3PAY_Cache_version_.'" rel="stylesheet">';

        return ['error' => 0, 'head'=>$head, 'CheckPaymentData' => $data, 'html'=>$this->getTemplateHtml(__DIR__.'/templates/checkPayment.php', $data)];
    }

    /**
     * @param array $data
     */
    public function showLoad($data=[]){
        // default params
        if(!isset($data['checkAuthRequired'])){ $data['checkAuthRequired'] = true; }

        $loadPage = $this->loadPage();
        echo json_encode($loadPage);
        exit;

    }

    /**
     * @return array
     */
    public function loadPage(){
        $dataPost = $_REQUEST;
        if(empty($dataPost['loadPage'])){
            return ['error' => 1, 'data' => 'loadPage is empty', 'html' => 'loadPage is empty'];
        }
        $MyClass = new wW3pay();
        $fun = 'show'.$dataPost['loadPage'];
        $ArrayParams=[];
        if (!method_exists($MyClass, $fun)) {
            return ['error' => 1, 'data' => 'function '.$fun.' not found', 'html' => 'function '.$fun.' not found'];
        }
        $resultFun = $MyClass->$fun(...$ArrayParams);
        return $resultFun;
    }

    /**
     * @param string $sendurl
     */
    public function showSaveSettings($sendurl=''){
        if(empty($sendurl)){ $sendurl = $_SERVER['REQUEST_URI']; }

        // Update
        if(!empty($_REQUEST['UpdateW3pay']) && !empty($_REQUEST['cms'])){
            $Update = sUpdate::instance()->startUpdate($_REQUEST['cms']);
            if(!empty($Update['error'])){
                echo json_encode($Update);
                exit;
            }
        }

        sLanguage::instance()->getLanguageBlock();

        // Delete all settings files
        if(!empty($_POST['deleteAllSettingsForm'])){
            $saveSettings = sAssistant::instance()->removeAllSettingsFiles($_POST);
            echo json_encode($saveSettings);
            exit;
        }

        // Save Password Settings
        if(!empty($_POST['savePasswordSettingsForm'])){
            $savePasswordSettings = sAuth::instance()->savePasswordSettings($_POST);
            echo json_encode($savePasswordSettings);
            exit;
        }

        // Create a settings file and save settings
        if(!empty($_POST['settingsW3payForm'])){
            $saveSettings = sAssistant::instance()->saveSettings($_POST);
            echo json_encode($saveSettings);
            exit;
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function showFormSettings($data=[]){
        // default params
        if(!isset($data['checkAuthRequired'])){ $data['checkAuthRequired'] = true; }
        if(!isset($data['cms'])){ $data['cms'] = 'none'; } // - none, wp, oc
        if(!isset($data['sendurl'])){ $data['sendurl'] = '/w3pay/w3payFrontend/load.php'; }

        $data['LanguageBlock'] = sLanguage::instance()->getLanguageBlock();
        $ParameterSymbol = (strpos($data['sendurl'], '?') === false)?'?':'&';
        $data['sendurl'] = $data['sendurl'].$ParameterSymbol.'lang='.$data['LanguageBlock']['activeLanguage']['ISOCode'];

        // Check Auth
        $FormAuth = wW3pay::instance()->showFormAuth($data['checkAuthRequired'], 'FormSettings', $data['sendurl']);
        if(!empty($FormAuth['error'])){ return $FormAuth; }

        $data['FolderFrontendUrl'] = _W3PAY_w3payFrontend_;
        $data['getPasswordSettingsHash'] = sAuth::instance()->getPasswordSettingsHash();
        $data['SettingsData'] = sAssistant::instance()->getSettingsData();
        $data['checkSettingsFile'] = sAssistant::instance()->checkSettingsFile();
        $data['VersionData'] = sUpdate::instance()->getVersionData();
        $data['UpdatesArr'] = sUpdate::instance()->getUpdatesArr();

        $head = '<meta name="viewport" content="width=device-width, initial-scale=1">';
        $head .= '<script type="text/javascript" src="'.$data['FolderFrontendUrl'].'/files/js/settings.js?cache='._W3PAY_Cache_version_.'"></script>';
        $head .= '<link href="'.$data['FolderFrontendUrl'].'/files/css/w3pay.css?cache='._W3PAY_Cache_version_.'" rel="stylesheet">';

        return ['error' => 0, 'head'=>$head, 'html'=>$this->getTemplateHtml(__DIR__.'/templates/formSettings.php', $data)];
    }

    /**
     * @return array
     */
    public function showSignOut(){
        sAuth::instance()->resetAuth();
        return ['error' => 0, 'data'=>'SignOut', 'html'=>'SignOut'];
    }

    /**
     * @return array
     */
    public function showUpdate(){
        // Check Auth
        $FormAuth = wW3pay::instance()->showFormAuth();
        if(!empty($FormAuth['error'])){ return $FormAuth; }

        return sUpdate::instance()->startUpdate();
    }

    /**
     * @param bool $checkAuthRequired
     * @param string $loadPage
     * @param string $sendurl
     * @return array
     */
    public function showFormAuth($checkAuthRequired=true, $loadPage='', $sendurl='/w3pay/w3payFrontend/load.php'){
        // if authorization is not required, then we have successfully authorized
        if(!$checkAuthRequired){
            // If access is without a password, then we will create a random password to lock the settings.
            sAuth::instance()->savePasswordSettings(['PasswordSettings'=>md5(sAssistant::instance()->generateRandomString())]);
            return sAuth::instance()->saveAuth();
        } else {
            $checkAuth = sAuth::instance()->checkAuth();
            if(empty($checkAuth['error'])){
                return $checkAuth;
            }
            if(!empty($_REQUEST['PasswordSettings'])){
                return $checkAuth;
            }
        }
        sAuth::instance()->resetAuth();
        $data=[];
        $data['loadPage'] = $loadPage;
        $data['LanguageBlock'] = sLanguage::instance()->getLanguageBlock();
        $data['FolderFrontendUrl'] = _W3PAY_w3payFrontend_;
        $data['getPasswordSettingsHash'] = sAuth::instance()->getPasswordSettingsHash();

        $ParameterSymbol = (strpos($sendurl, '?') === false)?'?':'&';
        $sendurl = $sendurl.$ParameterSymbol.'lang='.$data['LanguageBlock']['activeLanguage']['ISOCode'];
        $data['sendurl'] = $sendurl;

        $head = '<meta name="viewport" content="width=device-width, initial-scale=1">';
        $head .= '<script type="text/javascript" src="'.$data['FolderFrontendUrl'].'/files/js/settings.js?cache='._W3PAY_Cache_version_.'"></script>';
        $head .= '<script type="text/javascript" src="'.$data['FolderFrontendUrl'].'/files/js/transactions.js?cache='._W3PAY_Cache_version_.'"></script>';
        $head .= '<link href="'.$data['FolderFrontendUrl'].'/files/css/w3pay.css?cache='._W3PAY_Cache_version_.'" rel="stylesheet">';

        return ['error' => 1, 'data'=>'Auth required', 'head'=>$head, 'html'=>$this->getTemplateHtml(__DIR__.'/templates/auth.php', $data)];
    }

    /**
     * @return array
     */
    public function  showTransactionsPage(){
        // Check Auth
        $FormAuth = wW3pay::instance()->showFormAuth();
        if(!empty($FormAuth['error'])){ return $FormAuth; }

        if(!empty($_POST['pagelog'])){
            $dataPost = $_REQUEST;
            $checkAuth = sAuth::instance()->checkAuth();
            if(!empty($checkAuth['error'])){
                echo json_encode($checkAuth);
                exit;
            }

            include_once(_W3PAY_w3payBackend_ . '/services/sW3pay.php');
            $data=[];
            $data['PaymentSettings'] = sW3pay::instance()->getPaymentSettings();
            if(empty($data['PaymentSettings'])){
                exit;
            }
            $data['checkPaymentPageUrl'] = $_POST['checkPaymentPageUrl'];
            $data['LanguageBlock'] = sLanguage::instance()->getLanguageBlock();
            $data['TransactionsPage'] = sTransactions::instance()->getTransactionsPageArr($_POST['pagelog']);
            $data['StatusArr'] = sTransactions::instance()->getStatusArr();
            echo json_encode(['error' => 0, 'data'=>'success', 'html'=>$this->getTemplateHtml(__DIR__.'/templates/transactionsPage.php', $data)]);
            exit;
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function showTransactions($data=[]){
        // default params
        if(!isset($data['checkAuthRequired'])){ $data['checkAuthRequired'] = true; }
        if(!isset($data['sendurl'])){ $data['sendurl'] = _W3PAY_w3payFrontend_.'/load.php'; }

        $checkSettings = $this->checkSettings(_W3PAY_w3payFrontend_);
        if(!empty($checkSettings['error'])){
            return $checkSettings;
        }
        // Check Auth
        $FormAuth = wW3pay::instance()->showFormAuth($data['checkAuthRequired'], 'Transactions', $data['sendurl']);
        if(!empty($FormAuth['error'])){ return $FormAuth; }

        if($data['checkAuthRequired']){
            $checkAuth = sAuth::instance()->checkAuth();
            if(!empty($checkAuth['error'])){
                return $checkAuth;
            }
        }

        if(empty($data['checkPaymentPageUrl'])){
            $data['checkPaymentPageUrl'] = '/w3pay/w3payFrontend/checkPayment.php';
        }
        $data['FolderFrontendUrl'] = _W3PAY_w3payFrontend_;
        $data['LanguageBlock'] = sLanguage::instance()->getLanguageBlock();
        $data['TransactionsPages'] = sTransactions::instance()->getTransactionsPages();

        $head = '<meta name="viewport" content="width=device-width, initial-scale=1">';
        $head .= '<script type="text/javascript" src="'.$data['FolderFrontendUrl'].'/files/js/transactions.js?cache='._W3PAY_Cache_version_.'"></script>';
        $head .= '<link href="'.$data['FolderFrontendUrl'].'/files/css/w3pay.css?cache='._W3PAY_Cache_version_.'" rel="stylesheet">';

        return ['error' => 0, 'head'=>$head, 'html'=>$this->getTemplateHtml(__DIR__.'/templates/transactions.php', $data)];
    }

    /**
     * @param $FolderFrontendUrl
     * @return array
     */
    public function checkSettings($FolderFrontendUrl){
        if (!file_exists(__DIR__.'/../settings/sSettings.php')) {
            $LanguageBlock = sLanguage::instance()->getLanguageBlock();
            $errorText = $LanguageBlock['L']->sL('Perform the initial setup of the payment method').'. <a target="_blank" href="'.$FolderFrontendUrl.'/settings.php">Settings.php</a>';
            return ['error' => 1, 'html' => $errorText];
        }
        return ['error' => 0, 'html' => 'success'];
    }


    /**
     * @param $template
     * @param array $data
     * @return false|string
     */
    public function getTemplateHtml($template, $data = []){
        ob_start();
        try {
            include $template;
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

}