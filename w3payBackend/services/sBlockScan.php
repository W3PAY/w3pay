<?php
/**
 * W3PAY - Web3 Crypto Payments
 * Website: https://w3pay.dev
 * GitHub Website: https://w3pay.github.io/
 * GitHub: https://github.com/w3pay
 * Copyright (c)
 */

class sBlockScan
{

    protected static $instance;

    /**
     * @return sBlockScan
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @param $chainId
     * @return string[]
     */
    public function getScanApiTokenByChainId($chainId){
        $ScanApiTokens = sSettings::instance()->getScanApiTokens();
        if(!empty($ScanApiTokens[$chainId])){
            return $ScanApiTokens[$chainId];
        } else {
            return ['ScanApiUrl'=>'', 'ScanApiToken'=>''];
        }
    }

    /**
     * Returns information about a transaction requested by transaction hash.
     * @param $txhash
     * @return mixed
     */
    public function getTransactionByHash($chainId, $txhash)
    {
        $PaymentSettingsChain = sW3pay::instance()->getPaymentSettingsByChainId($chainId);
        $ScanApiTokenByChainId = $this->getScanApiTokenByChainId($chainId);
        if(!empty($PaymentSettingsChain)){
            sleep(1);
            $pairsInfo = file_get_contents('https://'.$ScanApiTokenByChainId['ScanApiUrl'].'/api?module=proxy&action=eth_getTransactionByHash&txhash=' . $txhash . '&apikey=' . $ScanApiTokenByChainId['ScanApiToken']);
            $pairsArr = json_decode($pairsInfo, true);
            return $pairsArr;
        } else {
            return [];
        }
    }

    /**
     * @param $chainId
     * @param $txhash
     * @return array|mixed
     */
    public function getTxReceiptStatus($chainId, $txhash){
        $PaymentSettingsChain = sW3pay::instance()->getPaymentSettingsByChainId($chainId);
        $ScanApiTokenByChainId = $this->getScanApiTokenByChainId($chainId);
        if(!empty($PaymentSettingsChain)){
            sleep(1);
            $pairsInfo = file_get_contents('https://'.$ScanApiTokenByChainId['ScanApiUrl'].'/api?module=transaction&action=gettxreceiptstatus&txhash='.$txhash.'&apikey='.$ScanApiTokenByChainId['ScanApiToken']);
            $pairsArr = json_decode($pairsInfo, true);
            return $pairsArr;
        } else {
            return [];
        }
    }

    /**
     * @param $chainId
     * @param $txhash
     * @return array|mixed
     */
    public function getTxReceiptStatusTest_ob_start($ScanApiUrl, $ScanApiToken, $txhash){
        ob_start();
        try {
            $pairsInfo = file_get_contents('https://'.$ScanApiUrl.'/api?module=transaction&action=gettxreceiptstatus&txhash='.$txhash.'&apikey='.$ScanApiToken);
            echo $pairsInfo;
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }

        $contents = ob_get_contents();             //  Instead, output above is saved to $contents
        ob_end_clean();
        return $contents;
    }

}