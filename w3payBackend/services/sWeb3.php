<?php
/**
 * W3PAY - Web3 Crypto Payments
 * Website: https://w3pay.dev
 * GitHub Website: https://w3pay.github.io/
 * GitHub: https://github.com/w3pay
 * Copyright (c)
 */

class sWeb3
{

    protected static $instance;

    /**
     * @param bool $composerAutoloadFile
     * @return sWeb3
     */
    public static function instance($composerAutoloadFile=false)
    {
        if (!isset(self::$instance)) {
            if(empty($composerAutoloadFile)){
                $composerAutoloadFile = sSettings::instance()->getSettingsFiles()['composerAutoload'];
            }
            if(file_exists($composerAutoloadFile)){
                require($composerAutoloadFile);
            } else {
                echo 'You use web3p. If $useWeb3 = true, then execute in the console: <pre>cd '.__DIR__ . '/../composer/'.'</pre> <pre>composer require web3p/web3.php dev-master</pre>';
                exit;
            }
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @param $chainId
     * @param $txhash
     * @return array
     */
    public function getTxReceiptStatus($chainId, $txhash){
        $PaymentSettingsChain = sW3pay::instance()->getPaymentSettingsByChainId($chainId);
        if(empty($PaymentSettingsChain['chainData']['rpcUrl'])){
            return ['status'=>0, 'message'=>'Error: chainData rpcUrl not found', 'result'=>['status'=>'']];
        }
        $jsontTxReceiptStatus =  $this->getTxReceiptStatus_ob_start($PaymentSettingsChain['chainData']['rpcUrl'], $txhash);

        $transaction = json_decode($jsontTxReceiptStatus, true);

        if(empty($transaction['status'])){
            return ['status'=>0, 'message'=>'Error: '.$jsontTxReceiptStatus, 'result'=>['status'=>'']];
        }
        if($transaction['status']=='0x1'){
            return ['status'=>1, 'message'=>'OK', 'result'=>['status'=>1]];
        } else {
            return ['status'=>1, 'message'=>'OK', 'result'=>['status'=>0]];
        }
    }

    /**
     * @param $rpcUrl
     * @param $txhash
     * @return false|string
     */
    public function getTxReceiptStatus_ob_start($rpcUrl, $txhash){
        ob_start();
        try {
            $web3 = new \Web3\Web3(new \Web3\Providers\HttpProvider(new \Web3\RequestManagers\HttpRequestManager($rpcUrl, 10)));

            $eth = $web3->eth;
            $eth->getTransactionReceipt($txhash, function ($err, $transaction) {
                if ($err !== null) {
                    echo $err;
                } else {
                    $transaction = json_encode($transaction);
                    echo $transaction;
                }
            });
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }

        $contents = ob_get_contents();             //  Instead, output above is saved to $contents
        ob_end_clean();
        return $contents;
    }

    /**
     * @param $chainId
     * @param $txhash
     * @return array
     */
    public function getTransactionByHash($chainId, $txhash){
        $PaymentSettingsChain = sW3pay::instance()->getPaymentSettingsByChainId($chainId);
        $jsontTxReceiptStatus =  $this->getTransactionByHash_ob_start($PaymentSettingsChain['chainData']['rpcUrl'], $txhash);

        $transaction = json_decode($jsontTxReceiptStatus, true);

        if(empty($transaction['hash'])){
            return ['status'=>0, 'message'=>'Error: '.$jsontTxReceiptStatus, 'result'=>[]];
        }
        return ['status'=>1, 'message'=>'OK', 'result'=>$transaction];
    }

    /**
     * @param $rpcUrl
     * @param $txhash
     * @return false|string
     */
    public function getTransactionByHash_ob_start($rpcUrl, $txhash){
        ob_start();

        try {
            $web3 = new \Web3\Web3(new \Web3\Providers\HttpProvider(new \Web3\RequestManagers\HttpRequestManager($rpcUrl, 10)));

            $eth = $web3->eth;
            $eth->getTransactionByHash($txhash, function ($err, $transaction) {
                if ($err !== null) {
                    echo $err;
                } else {
                    $transaction = json_encode($transaction);
                    echo $transaction;
                }
            });
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }

        $contents = ob_get_contents();             //  Instead, output above is saved to $contents
        ob_end_clean();
        return $contents;
    }

}