<?php
/**
 * W3PAY - Web3 Crypto Payments
 * Website: https://w3pay.dev
 * GitHub Website: https://w3pay.github.io/
 * GitHub: https://github.com/w3pay
 * Copyright (c)
 */

class sSettingsExample
{

    protected static $instance;

    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }
    public $returnExampleData = true;

    // TODO Settings start

    //Enter your details
    public $SecretSignKey = '{#SecretSignKey#}'; // Any phrase to create and verify a signature
    public $addressRecipient = '{#addressRecipient#}'; // '0x000.....' Address of the recipient(seller) eth
    public $LoadW3paySettingsDefaultGitHub = '{#LoadW3paySettingsDefaultGitHub#}'; // true - Get the latest w3pay_settings_default.json file from github
    public $useWeb3 = '{#useWeb3#}'; // true - Use web3p. A php interface for interacting with the Ethereum blockchain and ecosystem.
    // If $useWeb3 = true, then execute in the console: cd w3pay/w3payBackend/composer/; composer require web3p/web3.php dev-master;

    /*public function basicSettings()
    {
        $basicSettings = [
            'SecretSignKey' => '{#SecretSignKey#}',

        ];
    }*/

    // Required padding only if $useWeb3 = false
    // Register and get signature verification API for each block explorer and fill them. Function ScanApiTokens().
    public function getScanApiTokens()
    {
        if($this->returnExampleData){
            $ScanApiTokens = [
                97 => ['ScanApiUrl' => 'api-testnet.bscscan.com', 'ScanApiToken' => ''], // https://bscscan.com/apis Free API, BNB Smart Chain (BEP20) - Testnet
                56 => ['ScanApiUrl' => 'api.bscscan.com', 'ScanApiToken' => ''], // https://bscscan.com/apis Free API, BNB Smart Chain (BEP20)
                137 => ['ScanApiUrl' => 'api.polygonscan.com', 'ScanApiToken' => ''], // Polygon
                43114 => ['ScanApiUrl' => 'api.snowtrace.io', 'ScanApiToken' => ''], // Avalanche C-Chain
                250 => ['ScanApiUrl' => 'api.ftmscan.com', 'ScanApiToken' => ''], // Fantom Opera
            ];
        } else {
            $ScanApiTokens = '{#ScanApiTokens#}';
        }
        return $ScanApiTokens;
    }

    //Enter tokens to receive in chains. PaymentPersonalSettings() function.
    public function getPaymentPersonalSettings()
    {
        if($this->returnExampleData){
            $PaymentPersonalSettings = [
                97 => ['receiveToken' => ['nameCoin' => 'USDT', 'addressCoin' => '0x7ef95a0FEE0Dd31b22626fA2e10Ee6A223F8a684'], 'NetworkStatus' => 0], // BNB Smart Chain (BEP20) - Testnet
                56 => ['receiveToken' => ['nameCoin' => 'USDT', 'addressCoin' => '0x55d398326f99059fF775485246999027B3197955'], 'NetworkStatus' => 1], // BNB Smart Chain (BEP20)
                137 => ['receiveToken' => ['nameCoin' => 'USDT', 'addressCoin' => '0xc2132D05D31c914a87C6611C10748AEb04B58e8F'], 'NetworkStatus' => 1], // Polygon
                43114 => ['receiveToken' => ['nameCoin' => 'USDT', 'addressCoin' => '0x9702230A8Ea53601f5cD2dc00fDBc13d4dF4A8c7'], 'NetworkStatus' => 1], // Avalanche C-Chain
                250 => ['receiveToken' => ['nameCoin' => 'USDT', 'addressCoin' => '0x049d68029688eAbF473097a2fC38ef61633A3C7A'], 'NetworkStatus' => 1], // Fantom Opera
            ];
        } else {
            $PaymentPersonalSettings = '{#PaymentPersonalSettings#}';
        }
        return $PaymentPersonalSettings;
    }

    // Set 'w3pay_settings' to the path to the /w3pay/w3pay/w3payFrontend/files/settings/w3pay_settings.json.
    public function getSettingsFiles()
    {
        if($this->returnExampleData){
            $SettingsFiles = [
                'w3pay_settings' => sDefines::instance()->getPaths()['w3payFrontendPath']. '/files/settings/w3pay_settings.json',
                'w3pay_settings_default' => __DIR__ . '/w3pay_settings_default.json',
                'composerAutoload' => __DIR__ . '/../composer/vendor/autoload.php',
            ];
        } else {
            $SettingsFiles = '{#SettingsFiles#}';
        }
        return $SettingsFiles;
    }

    // TODO Settings the end
}