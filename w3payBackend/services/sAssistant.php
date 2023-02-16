<?php
/**
 * W3PAY - Web3 Crypto Payments
 * Website: https://w3pay.dev
 * GitHub Website: https://w3pay.github.io/
 * GitHub: https://github.com/w3pay
 * Copyright (c)
 */

include_once(__DIR__.'/../services/sLanguage.php');
include_once(__DIR__.'/../services/sDefines.php');
include_once(__DIR__.'/../services/sAuth.php');
include_once(__DIR__.'/../services/sFiles.php');

class sAssistant
{

    /**
     * sAssistant constructor.
     */
    function __construct() {
        sDefines::instance()->checkDefines();
    }

    protected static $instance;

    /**
     * @return sAssistant
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @return string[]
     */
    public function getFilesSettingsArr(){
        $FilesSettingsArr = [
            'sSettings.php' => __DIR__ . '/../settings/sSettings.php',
            'PasswordSettingsHash.php' => __DIR__ . '/../settings/PasswordSettingsHash.php',
            'installSh' => __DIR__ . '/install.sh',
        ];
        return $FilesSettingsArr;
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

    /**
     * @param $dataPost
     * @return array
     */
    public function removeAllSettingsFiles($dataPost){
        $checkAuth = sAuth::instance()->checkAuth();
        if(!empty($checkAuth['error'])){
            return $checkAuth;
        }

        $this->getFilesSettingsArr()['sSettings.php'];
        $removeText = '';
        if(file_exists($this->getFilesSettingsArr()['sSettings.php'])){
            unlink($this->getFilesSettingsArr()['sSettings.php']);
            $removeText .= 'Remove '.$this->getFilesSettingsArr()['sSettings.php'].' file; ';
        }
        if(file_exists(sAuth::instance()->PasswordSettingsHashPath)){
            unlink(sAuth::instance()->PasswordSettingsHashPath);
            $removeText .= 'Remove '.sAuth::instance()->PasswordSettingsHashPath.' file; ';
        }
        if(file_exists($dataPost['SettingsFiles']['w3pay_settings'])){
            unlink($dataPost['SettingsFiles']['w3pay_settings']);
            $removeText .= 'Remove '.$dataPost['SettingsFiles']['w3pay_settings'].' file; ';
        }
        sFiles::instance()->removeDir(__DIR__.'/../composer');
        sFiles::instance()->removeDir(__DIR__.'/../paymentsLog');
        sFiles::instance()->removeDir(__DIR__.'/../updates');
        sFiles::instance()->removeDir(__DIR__.'/../settings');
        sFiles::instance()->removeDir(sDefines::instance()->getPaths()['w3payFrontendPath'].'/files/settings');
        return ['error' => 0, 'data' => $removeText.' <meta http-equiv="refresh" content="0">'];
    }

    /**
     * @param $dataPost
     * @param bool $checkAuthRequired
     * @return array
     */
    public function saveSettings($dataPost){
        $checkAuth = sAuth::instance()->checkAuth();
        if(!empty($checkAuth['error'])){
            return $checkAuth;
        }
        if(empty($dataPost['SecretSignKey'])){
            return ['error' => 1, 'data' => 'SecretSignKey is empty'];
        }
        if (mb_strlen($dataPost['SecretSignKey'], 'UTF-8') < 8) {
            return ['error' => 1, 'data' => 'SecretSignKey less than 8 characters'];
        }
        if(empty($dataPost['addressRecipient'])){
            return ['error' => 1, 'data' => 'addressRecipient is empty'];
        }
        if (mb_strtolower(substr($dataPost['addressRecipient'], 0, 2)) != mb_strtolower('0x')) {
            return ['error' => 1, 'data' => 'addressRecipient start not 0x'];
        }
        if (mb_strlen($dataPost['addressRecipient'], 'UTF-8') != 42) {
            return ['error' => 1, 'data' => 'addressRecipient not 40 symbols'];
        }
        if(empty($dataPost['ScanApiTokens'])){
            return ['error' => 1, 'data' => 'ScanApiTokens is empty'];
        }
        if(empty($dataPost['receiveTokensAddresses'])){
            return ['error' => 1, 'data' => 'receiveTokensAddresses is empty'];
        }
        if(empty($dataPost['SettingsFiles'])){
            return ['error' => 1, 'data' => 'SettingsFiles is empty'];
        }
        foreach ($dataPost['SettingsFiles'] as $SettingsKey => $SettingsFile){
            if(empty($dataPost['SettingsFiles'])){
                return ['error' => 1, 'data' => 'SettingsFiles '.$SettingsKey.' is empty'];
            }
        }

        if(empty($dataPost['LoadW3paySettingsDefaultGitHub'])){
            $dataPost['LoadW3paySettingsDefaultGitHub'] = 'false';
        }
        if(!empty($dataPost['UpdateW3paySettingsDefaultGitHub']) && !empty($dataPost['SettingsFiles']['w3pay_settings_default'])){
            $w3pay_settings_default_json = file_get_contents('https://w3pay.github.io/w3pay/w3payBackend/settings/w3pay_settings_default.json');
            file_put_contents($dataPost['SettingsFiles']['w3pay_settings_default'], $w3pay_settings_default_json);
        }
        if(!file_exists($dataPost['SettingsFiles']['w3pay_settings_default'])){
            return ['error' => 1, 'data' => 'SettingsFiles '.$dataPost['SettingsFiles']['w3pay_settings_default'].' not found'];
        }

        $PaymentPersonalSettings=[];
        foreach ($dataPost['receiveTokensAddresses'] as $chainId => $receiveToken){
            $PaymentPersonalSettings[$chainId]['receiveToken']['addressCoin'] = $receiveToken;
            if(!empty($dataPost['NetworksStatus'][$chainId])){
                $PaymentPersonalSettings[$chainId]['NetworkStatus'] = 1;
            } else {
                $PaymentPersonalSettings[$chainId]['NetworkStatus'] = 0;
            }
        }

        $CheckNetworks = '';
        $w3pay_settings_default = json_decode(file_get_contents($dataPost['SettingsFiles']['w3pay_settings_default']), true);
        if(empty($dataPost['useWeb3'])){
            $dataPost['useWeb3'] = 'false';

            foreach ($dataPost['ScanApiTokens'] as $chainId => $ScanApiToken){
                if(!empty($PaymentPersonalSettings[$chainId]['NetworkStatus'])){
                    if(empty($ScanApiToken['ScanApiToken'])){
                        return ['error' => 1, 'data' => 'Enter Api token '.$ScanApiToken['ScanApiUrl'].' or enable web3p.'];
                    }
                }
            }

            // Check Networks API
            include_once(__DIR__ . '/../services/sBlockScan.php');
            foreach ($w3pay_settings_default['chainsData'] as $chainData){
                if(!empty($PaymentPersonalSettings[$chainData['chainData']['chainId']]['NetworkStatus'])){
                    $jsontTxReceiptStatus =  sBlockScan::instance()->getTxReceiptStatusTest_ob_start($dataPost['ScanApiTokens'][$chainData['chainData']['chainId']]['ScanApiUrl'], $dataPost['ScanApiTokens'][$chainData['chainData']['chainId']]['ScanApiToken'], '0x0000000000000000000000000000000000000000000000000000000000000001');
                    $TxReceiptStatusArr = json_decode($jsontTxReceiptStatus, true);
                    if(empty($TxReceiptStatusArr['status'])){
                        $PaymentPersonalSettings[$chainData['chainData']['chainId']]['NetworkStatus']=0;
                        $CheckNetworks .= '<br>BlockScan: Invalid API Key '.$dataPost['ScanApiTokens'][$chainData['chainData']['chainId']]['ScanApiUrl'].'. Network '.$chainData['chainData']['chainName'].' Forced Disabled; ';
                    }
                }
            }

        } else {
            $installComposer = sAssistant::instance()->installComposer();
            if(!empty($installComposer['error'])){
                return $installComposer;
            }

            $composerCommand = 'sh '.$this->getFilesSettingsArr()['installSh'];
            if(empty($dataPost['SettingsFiles']['composerAutoload'])){
                return ['error' => 1, 'data' => 'Required to use Web3 backend checks. Path to composerAutoload file is empty'];
            }
            if(!file_exists($dataPost['SettingsFiles']['composerAutoload'])){
                return ['error' => 1, 'data' => 'Required to use Web3 backend checks. Composer not found. Execute in the console: '.$composerCommand];
            }

            // Check Networks
            include_once(__DIR__ . '/../services/sWeb3.php');
            foreach ($w3pay_settings_default['chainsData'] as $chainData){
                if(!empty($PaymentPersonalSettings[$chainData['chainData']['chainId']]['NetworkStatus'])){
                    $jsontTxReceiptStatus =  sWeb3::instance($dataPost['SettingsFiles']['composerAutoload'])->getTxReceiptStatus_ob_start($chainData['chainData']['rpcUrl'], '0x0000000000000000000000000000000000000000000000000000000000000001');
                    if($jsontTxReceiptStatus!=='null'){
                        $PaymentPersonalSettings[$chainData['chainData']['chainId']]['NetworkStatus']=0;
                        $CheckNetworks .= '<br>web3p: Failed connect to '.$chainData['chainData']['rpcUrl'].'. Network '.$chainData['chainData']['chainName'].' Forced Disabled; ';
                    }
                }
            }
        }

        //$getPasswordSettingsHash = sAuth::instance()->getPasswordSettingsHash();
        if(!empty($dataPost['PasswordSettingsNew'])){
            if (mb_strlen($dataPost['PasswordSettingsNew'], 'UTF-8') < 8) {
                return ['error' => 1, 'data' => 'PasswordSettings less than 8 characters'];
            }
            $PasswordSettingsHash = sAuth::instance()->getHash($dataPost['PasswordSettingsNew']);
            sAuth::instance()->savePasswordSettingsHash($PasswordSettingsHash);
        }

        // create a settings file and replace the parameters
        $sSettingsText = file_get_contents(__DIR__ . '/sSettingsExample.php');
        $sSettingsText = str_replace("sSettingsExample", "sSettings", $sSettingsText);
        $sSettingsText = str_replace('$returnExampleData = true;', '$returnExampleData = false;', $sSettingsText);

        $sSettingsText = str_replace("{#SecretSignKey#}", $dataPost['SecretSignKey'], $sSettingsText);
        $sSettingsText = str_replace("{#addressRecipient#}", $dataPost['addressRecipient'], $sSettingsText);
        $sSettingsText = str_replace("'{#LoadW3paySettingsDefaultGitHub#}'", $dataPost['LoadW3paySettingsDefaultGitHub'], $sSettingsText);
        $sSettingsText = str_replace("'{#useWeb3#}'", $dataPost['useWeb3'], $sSettingsText);

        $sSettingsText = str_replace("'{#ScanApiTokens#}'", sAssistant::instance()->getBufferArr($dataPost['ScanApiTokens']), $sSettingsText);
        $sSettingsText = str_replace("'{#PaymentPersonalSettings#}'", sAssistant::instance()->getBufferArr($PaymentPersonalSettings), $sSettingsText);
        $sSettingsText = str_replace("'{#SettingsFiles#}'", sAssistant::instance()->getBufferArr($dataPost['SettingsFiles']), $sSettingsText);

        $fp = fopen($this->getFilesSettingsArr()['sSettings.php'], "w");
        fwrite($fp, $sSettingsText);
        fclose($fp);

        // generate w3pay_settings.json settings file.
        include_once(__DIR__ . '/../services/sW3pay.php');
        $JsonSettings = sW3pay::instance()->generateJsonSettings(sSettings::instance()->getSettingsFiles()['w3pay_settings']);
        if(!empty($JsonSettings['error'])){
            return $JsonSettings;
        }

        if(!empty($CheckNetworks)){
            return ['error' => 1, 'data' => 'The settings were saved with errors.<br>Errors: '.$CheckNetworks];
        }

        return ['error' => 0, 'data' => 'Success'];
    }

    /**
     * @return mixed
     */
    public function getSettingsData(){
        // Getting the current settings
        if (file_exists($this->getFilesSettingsArr()['sSettings.php'])) {
            include_once($this->getFilesSettingsArr()['sSettings.php']);

            //basicSettings
            $SecretSignKey = sSettings::instance()->SecretSignKey;
            $addressRecipient = sSettings::instance()->addressRecipient;
            $LoadW3paySettingsDefaultGitHub = sSettings::instance()->LoadW3paySettingsDefaultGitHub;
            $useWeb3 = sSettings::instance()->useWeb3;

            $ScanApiTokens = sSettings::instance()->getScanApiTokens();
            $PaymentPersonalSettings = sSettings::instance()->getPaymentPersonalSettings();
            $SettingsFiles = sSettings::instance()->getSettingsFiles();
        } else {
            // If there are no settings, then we get an example of settings
            include_once(__DIR__ . '/sSettingsExample.php');

            //basicSettings
            $SecretSignKey = sAssistant::instance()->generateRandomString();
            $addressRecipient = '';
            $LoadW3paySettingsDefaultGitHub = false;
            $useWeb3 = true;

            $ScanApiTokens = sSettingsExample::instance()->getScanApiTokens();
            $PaymentPersonalSettings = sSettingsExample::instance()->getPaymentPersonalSettings();
            $SettingsFiles = sSettingsExample::instance()->getSettingsFiles();
        }

        $data['SecretSignKey']=$SecretSignKey;
        $data['addressRecipient']=$addressRecipient;
        $data['LoadW3paySettingsDefaultGitHub']=$LoadW3paySettingsDefaultGitHub;
        $data['useWeb3']=$useWeb3;
        $data['ScanApiTokens']=$ScanApiTokens;
        $data['PaymentPersonalSettings']=$PaymentPersonalSettings;
        $data['SettingsFiles']=$SettingsFiles;
        $data['SettingsDefaultFileArr'] = json_decode(file_get_contents('https://w3pay.github.io/w3pay/w3payBackend/settings/w3pay_settings_default.json'), 'true');

        return $data;
    }

    /**
     * @return bool
     */
    public function checkSettingsFile(){
        if (file_exists($this->getFilesSettingsArr()['sSettings.php'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $arr
     * @return false|string
     */
    public function getBufferArr($arr)
    {
        ob_start();                                //  Let's start output buffering.
        var_export($arr);
        $contents = ob_get_contents();             //  Instead, output above is saved to $contents
        ob_end_clean();
        return $contents;
    }

    /**
     * @param int $length
     * @return string
     */
    function generateRandomString($length = 20) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @return array
     */
    public function installComposer(){

        if(!file_exists(__DIR__.'/../composer/vendor/autoload.php')){

            $dir = __DIR__.'/../composer';

            if (!file_exists($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    return ['error' => 1, 'data' => 'Need to create a folder '.$dir.'.'];
                }
            }

            $text = '{"config":{"platform-check": false},"require": {"web3p/web3.php": "dev-master"}}';
            if(!file_put_contents($dir.'/composer.json', $text)){
                return ['error' => 1, 'data' => 'File '.$dir.'/composer.json not created.'];
            }

            $PHP_BINDIR_p = 'php';
            if(!empty(getenv('PHPBIN'))){
                $PHP_BINDIR_p = getenv('PHPBIN'); // windows
                $PHP_BINDIR_p = str_replace('\\', "/", $PHP_BINDIR_p);
            } else {
                if(!empty(PHP_BINARY)){
                    $PHP_BINDIR_p = PHP_BINDIR.'/php'; // linux
                }
            }

            $text = '#!/bin/bash'.PHP_EOL;
            $text .= 'BASEDIR=$(dirname $0) # path to current directory'.PHP_EOL;
            $text .= '#mkdir -p ${BASEDIR}/../composer # create composer folder'.PHP_EOL;
            $text .= 'cd ${BASEDIR}/../composer # go to composer folder'.PHP_EOL;
            $text .= $PHP_BINDIR_p.' -r "copy(\'https://getcomposer.org/installer\', \'composer-setup.php\');"'.PHP_EOL;
            $text .= 'export COMPOSER_HOME=${BASEDIR}/../composer/cachecomposer;'.PHP_EOL;
            $text .= $PHP_BINDIR_p.' composer-setup.php'.PHP_EOL;
            $text .= $PHP_BINDIR_p.' -r "unlink(\'composer-setup.php\');"'.PHP_EOL;
            $text .= $PHP_BINDIR_p.' composer.phar install # Install composer'.PHP_EOL;
            $text .= 'rm -r ${BASEDIR}/../composer/cachecomposer'.PHP_EOL;
            $text .= '#composer require web3p/web3.php dev-master # Installing the web3p library'.PHP_EOL;

            $composerInstallFileTemp = $dir.'/composer_install_temp.sh';

            if(!file_put_contents($composerInstallFileTemp, $text)){
                return ['error' => 1, 'data' => 'File '.$composerInstallFileTemp.' not created.'];
            }

            $getFilePermsFile = substr(decoct(fileperms($composerInstallFileTemp)), -4);
            if($getFilePermsFile!='0744'){
                if(!chmod($composerInstallFileTemp, 0744)){
                    return ['error' => 1, 'data' => 'File '.$composerInstallFileTemp.'. Permissions required 744.'];
                }
            }

            $commandSH = $composerInstallFileTemp.' > '.$dir.'/composerLog 2>&1 &';
            if(!shell_exec($commandSH)){
                //return ['error' => 1, 'data' => 'To install composer, you need to execute in the console: '.$composerInstallFileTemp];
            }
            sleep(15);
            if(!file_exists(__DIR__.'/../composer/vendor/autoload.php')){
                return ['error' => 1, 'data' => 'To install composer, you need to execute in the console: '.$composerInstallFileTemp];
            }
        }

        return ['error' => 0, 'data' => 'Success'];
    }

}