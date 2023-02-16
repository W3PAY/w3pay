<?php
/**
 * W3PAY - Web3 Crypto Payments
 * Website: https://w3pay.dev
 * GitHub Website: https://w3pay.github.io/
 * GitHub: https://github.com/w3pay
 * Copyright (c)
 */

class sAuth
{

    protected static $instance;

    /**
     * @return sAuth
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public $PasswordSettingsHashPath = __DIR__.'/../settings/PasswordSettingsHash.php';

    /**
     * @param bool $PasswordSettings
     * @return bool
     */
    public function resetAuth($PasswordSettings=false){
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if(session_status() === PHP_SESSION_ACTIVE) {
            if(!empty($_SESSION['w3pay']['auth'])){
                unset($_SESSION['w3pay']['auth']);
            }
        }
        return true;
    }

    /**
     * @return array
     */
    public function saveAuth(){
        $sessionStart = $this->sessionStart();
        if(!empty($sessionStart['error'])){ return $sessionStart; }
        $_SESSION['w3pay']['auth']=1;
        return ['error' => 0, 'data' => 'Auth success', 'html' => 'Auth success'];
    }

    /**
     * @return array
     */
    public function sessionStart(){
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if(session_status() !== PHP_SESSION_ACTIVE) {
            return ['error' => 1, 'data' => 'PHP SESSION NOT ACTIVE', 'html' => 'PHP SESSION NOT ACTIVE'];
        }
        return ['error' => 0, 'data' => 'PHP SESSION ACTIVE', 'html' => 'PHP SESSION ACTIVE'];
    }

    /**
     * @param bool $PasswordSettings
     * @return array
     */
    public function checkAuth($PasswordSettings=false){
        $getPasswordSettingsHash = sAuth::instance()->getPasswordSettingsHash();
        if(empty($getPasswordSettingsHash)){
            return ['error' => 1, 'data' => 'PasswordSettingsHash not found', 'html' => 'File PasswordSettingsHash not found'];
        }
        if(!empty($_REQUEST['PasswordSettings']) && !$PasswordSettings){
            $PasswordSettings = $_REQUEST['PasswordSettings'];
        }
        $sessionStart = $this->sessionStart();
        if(!empty($sessionStart['error'])){ return $sessionStart; }

        if(!empty($_SESSION['w3pay']['auth'])){
            return ['error' => 0, 'data' => 'Auth success', 'html' => 'Auth success'];
        }
        if(empty($PasswordSettings)){
            return ['error' => 1, 'data' => 'PasswordSettings is empty', 'html' => 'PasswordSettings is empty'];
        }
        $checkPasswordSettings = sAuth::instance()->checkPasswordSettings($PasswordSettings);
        if(!$checkPasswordSettings){
            return ['error' => 1, 'data' => 'Authentication failed!', 'html' => 'Authentication failed!'];
        }
        $this->saveAuth();
        return ['error' => 0, 'data' => 'Auth success', 'html' => 'Auth success'];
    }

    /**
     * @param $PasswordSettings
     * @return bool
     */
    public function checkPasswordSettings($PasswordSettings){
        if(empty($PasswordSettings)){
            return false;
        }
        return $this->checkPasswordSettingsHash(sAuth::instance()->getHash($PasswordSettings));
    }

    /**
     * @param $PasswordSettingsHash
     * @return bool
     */
    public function checkPasswordSettingsHash($PasswordSettingsHash){
        $getPasswordSettingsHash = sAuth::instance()->getPasswordSettingsHash();
        if(empty($getPasswordSettingsHash)){
            return false;
        }
        if($PasswordSettingsHash == $getPasswordSettingsHash){
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $dataPost
     * @return array
     */
    public function savePasswordSettings($dataPost){

        if(empty($dataPost['PasswordSettings'])){
            return ['error' => 1, 'data' => 'PasswordSettings is empty'];
        }
        if (mb_strlen($dataPost['PasswordSettings'], 'UTF-8') < 8) {
            return ['error' => 1, 'data' => 'PasswordSettings less than 8 characters'];
        }
        $getPasswordSettingsHash = $this->getPasswordSettingsHash();
        if(empty($getPasswordSettingsHash)){
            $PasswordSettingsHash = $this->getHash($dataPost['PasswordSettings']);
            $this->savePasswordSettingsHash($PasswordSettingsHash);
        }

        return ['error' => 0, 'data' => 'Success <meta http-equiv="refresh" content="0">'];
    }

    /**
     * @return string|string[]
     */
    public function getPasswordSettingsHash(){
        $PasswordSettingsHash = '';
        if (file_exists(sAuth::instance()->PasswordSettingsHashPath)) {
            $PasswordSettingsHash = file_get_contents(sAuth::instance()->PasswordSettingsHashPath);
            $PasswordSettingsHash = str_replace("<?php //", "", $PasswordSettingsHash);;
        }
        return $PasswordSettingsHash;
    }

    /**
     * @param $password
     * @return string
     */
    public function getHash($password){
        return hash('sha384', $password);
    }

    /**
     * @param $PasswordSettingsHash
     * @return array
     */
    public function savePasswordSettingsHash($PasswordSettingsHash){
        if(!sFiles::instance()->createFile(sAuth::instance()->PasswordSettingsHashPath, "<?php //".$PasswordSettingsHash)){
            return ['error' => 1, 'data' => 'Failed to create file: '.sAuth::instance()->PasswordSettingsHashPath];
        }
        return ['error' => 0, 'data' => 'Success'];
    }

}