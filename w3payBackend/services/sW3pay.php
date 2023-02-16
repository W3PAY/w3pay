<?php
/**
 * W3PAY - Web3 Crypto Payments
 * Website: https://w3pay.dev
 * GitHub Website: https://w3pay.github.io/
 * GitHub: https://github.com/w3pay
 * Copyright (c)
 */

include_once(__DIR__.'/../services/sDefines.php');
include_once('sAbi.php');
include_once('sWeb3.php');
include_once('sBlockScan.php');
include_once('sLanguage.php');
include_once('sTransactions.php');

class sW3pay
{
    public $sSettingsErrorText = '<h2>Perform the initial setup of the payment method.</h2>';

    /**
     * sW3pay constructor.
     */
    function __construct() {
        sDefines::instance()->checkDefines();
        if(file_exists(__DIR__.'/../settings/sSettings.php')){
            include_once(__DIR__.'/../settings/sSettings.php');
        }
    }

    protected static $instance;

    /**
     * @return sW3pay
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
     * @param string $addressSender
     * @return mixed
     */
    public function getCashbackData($chainId, $addressSender = 'addressSender'){
        $PaymentSettingsByChainId = $this->getPaymentSettingsByChainId($chainId);

        $CashbackData = $PaymentSettingsByChainId['cashbackData'];

        $CashbackLink = $PaymentSettingsByChainId['chainData']['BlockExplorerURL'].'/token/'.$CashbackData['token']['addressCoin'].'?a='.$addressSender;
        if (base64_decode($CashbackData['token']['addressCoin']) === base64_decode($PaymentSettingsByChainId['contractSwapRouter']['WETH'])) {
            $CashbackLink = $PaymentSettingsByChainId['chainData']['BlockExplorerURL'].'/address/'.$addressSender.'#internaltx';
        }
        $CashbackData['CashbackLink'] = $CashbackLink;

        return $CashbackData;
    }

    /**
     * get Payment Settings
     * @return array[]
     */
    public function getPaymentSettings()
    {
        if (!class_exists('sSettings')) {
            echo $this->sSettingsErrorText;
            return [];
        }

        $PaymentSettings = $this->getSettingsDefaultFileArr(sSettings::instance()->getSettingsFiles()['w3pay_settings_default']);

        if(empty($PaymentSettings)){
            echo 'File empty '.sSettings::instance()->getSettingsFiles()['w3pay_settings_default'].'';
            return [];
        }

        $PaymentPersonalSettings = sSettings::instance()->getPaymentPersonalSettings();

        //Replace receiveTokens and data
        foreach ($PaymentSettings['chainsData'] as $key => $chainData){
            //Replace PersonalSettings
            foreach ($chainData as $chainDataKey => $chainDataRow){
                if(!empty($PaymentPersonalSettings[$chainData['chainData']['chainId']][$chainDataKey])){
                    $PaymentSettings['chainsData'][$key][$chainDataKey] = $PaymentPersonalSettings[$chainData['chainData']['chainId']][$chainDataKey];
                }
            }

            // Add Data
            $PaymentSettings['chainsData'][$key]['addressRecipient'] = sSettings::instance()->addressRecipient; // Address of the recipient

            if(empty($PaymentPersonalSettings[$chainData['chainData']['chainId']]['NetworkStatus'])){
                $PaymentSettings['chainsData'][$key]['chainData']['status'] = 0;
            } else {
                $PaymentSettings['chainsData'][$key]['chainData']['status'] = 1;
            }
        }

        $TokensList = $this->getTokensList();

        // Add abiCoin
        foreach ($PaymentSettings['chainsData'] as $key => $info) {
            $chainId = $info['chainData']['chainId'];

            $TypesName = ['receiveToken','contractSwapRouter','contractPayData'];
            foreach ($TypesName as $TypeName){
                if(!empty($TokensList[$chainId][mb_strtolower($info[$TypeName]['addressCoin'])])){
                    $PaymentSettings['chainsData'][$key][$TypeName] = $TokensList[$chainId][mb_strtolower($info[$TypeName]['addressCoin'])];
                }
            }

            foreach ($info['payTokens'] as $payKey => $payToken) {
                if(!empty($TokensList[$chainId][mb_strtolower($payToken['addressCoin'])])){
                    $PaymentSettings['chainsData'][$key]['payTokens'][$payKey] = $TokensList[$chainId][mb_strtolower($payToken['addressCoin'])];
                }
            }
        }

        return $PaymentSettings;
    }

    /**
     * @param $chainId
     * @return array|mixed
     */
    public function getPaymentSettingsByChainId($chainId)
    {
        $PaymentSettingsChain=[];
        $PaymentSettingsArr = $this->getPaymentSettings();
        foreach($PaymentSettingsArr['chainsData'] as $PaymentSettingsRow){
            if(!empty($PaymentSettingsRow['chainData']['chainId']) && $PaymentSettingsRow['chainData']['chainId']==$chainId){
                $PaymentSettingsChain = $PaymentSettingsRow;
            }
        }
        return $PaymentSettingsChain;
    }

    /**
     * @param $fileP
     * @return array|mixed
     */
    public function getSettingsDefaultFileArr($fileP){
        $SettingsDefaultFileArr=[];
        if(file_exists($fileP)){
            $SettingsDefaultFileArr = json_decode(file_get_contents($fileP), 'true');
        }

        if (class_exists('sSettings')) {
            if(sSettings::instance()->LoadW3paySettingsDefaultGitHub){
                if(!empty($_SESSION['SettingsDefaultFileArr'])){
                    $SettingsDefaultFileArr = $_SESSION['SettingsDefaultFileArr'];
                } else {
                    $SettingsDefaultFileArr = json_decode(file_get_contents('https://w3pay.github.io/w3pay/w3payBackend/settings/w3pay_settings_default.json'), 'true');
                    $_SESSION['SettingsDefaultFileArr'] = $SettingsDefaultFileArr;
                }
            }
        }
        return $SettingsDefaultFileArr;
    }

    /**
     * @return array
     */
    public function getTokensList(){
        $TokensList=[];
        $PaymentSettings = $this-> getSettingsDefaultFileArr(sSettings::instance()->getSettingsFiles()['w3pay_settings_default']);
        if(!empty($PaymentSettings['chainsData'])){
            foreach ($PaymentSettings['chainsData'] as $chainDataRow){
                $chainId = $chainDataRow['chainData']['chainId'];

                $TypesName = ['contractSwapRouter','contractPayData'];
                foreach ($TypesName as $TypeName){
                    if(!empty($chainDataRow[$TypeName]['addressCoin'])){
                        $TokensList[$chainId][mb_strtolower($chainDataRow[$TypeName]['addressCoin'])]=$chainDataRow[$TypeName];
                    }
                }
                if(!empty($chainDataRow['payTokens'])){
                    foreach($chainDataRow['payTokens'] as $payToken){
                        $TokensList[$chainId][mb_strtolower($payToken['addressCoin'])]=$payToken;
                    }
                }
            }
        }
        return $TokensList;
    }

    /**
     * create file w3pay_settings.json
     * @param bool $dir
     */
    public function generateJsonSettings($pathFile)
    {
        $PaymentSettings = sW3pay::instance()->getPaymentSettings();
        $PaySettingsJson = json_encode($PaymentSettings);

        $dir = pathinfo($pathFile,PATHINFO_DIRNAME);
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0755, true)) {
                return ['error' => 0, 'data' => 'Failed to create folder '.$pathFile];
            }
        }

        $fp = fopen($pathFile, "w");
        fwrite($fp, $PaySettingsJson);
        fclose($fp);

        return ['error' => 0, 'data' => 'Success generate '.$pathFile];
    }

    /**
     * Add SignStr, addressRecipient from Order Data
     * @param $SelectPayTokensData
     * @return mixed
     */
    public function getOrderDataAddData($SelectPayTokensData)
    {
        if (!class_exists('sSettings')) {
            echo $this->sSettingsErrorText;
            return [];
        }

        $PaymentPersonalSettings = sSettings::instance()->getPaymentPersonalSettings();

        if(!empty($SelectPayTokensData['orderId'])){
            // Check pay order
            $PaymentLog = sTransactions::instance()->getTransaction($SelectPayTokensData['orderId']);
            $SelectPayTokensData['info']=['paid'=>0, 'data'=>[]];
            if(!empty($PaymentLog)){
                $SelectPayTokensData['info'] = ['paid'=>1, 'data'=>$PaymentLog];
            }
            if(!empty($SelectPayTokensData['payAmounts'])){
                // Check Network
                foreach ($SelectPayTokensData['payAmounts'] as $payAmountKey => $payAmount){
                    if(empty($PaymentPersonalSettings[$payAmount['chainId']]['NetworkStatus'])){
                        unset($SelectPayTokensData['payAmounts'][$payAmountKey]);
                    }
                }
                foreach ($SelectPayTokensData['payAmounts'] as $payAmountKey => $payAmount){
                    $PaymentSettingsChain = $this->getPaymentSettingsByChainId($payAmount['chainId']);
                    if(!empty($PaymentSettingsChain)){
                        $addressSwapRouter = $PaymentSettingsChain['contractSwapRouter']['addressCoin'];
                        $addressRecipientToken = $PaymentSettingsChain['receiveToken']['addressCoin'];
                        $payAmountInReceiveToken_decimals = $this->eth2wei($payAmount['payAmountInReceiveToken'], $PaymentSettingsChain['receiveToken']['decimals']);
                        $addressContractPayData = $PaymentSettingsChain['contractPayData']['addressCoin'];
                        $SelectPayTokensData['payAmounts'][$payAmountKey]['SignStr'] = sW3pay::instance()->generatePaymentInfo($SelectPayTokensData['orderId'], $payAmount['chainId'], $addressContractPayData, sSettings::instance()->addressRecipient, $addressRecipientToken, $payAmountInReceiveToken_decimals, $addressSwapRouter);
                    }
                }

                $SelectPayTokensData['payAmounts'] = array_values($SelectPayTokensData['payAmounts']);
            }
        }

        return $SelectPayTokensData;
    }

    /** check Sign Transaction By Hash
     * @param $txhash
     * @return array
     */
    public function checkSign($chainId, $txhash)
    {
        if (!class_exists('sSettings')) {
            echo $this->sSettingsErrorText;
            return [];
        }

        if(sSettings::instance()->useWeb3){
            $getTxReceiptStatus = sWeb3::instance()->getTxReceiptStatus($chainId, $txhash);
        } else {
            $getTxReceiptStatus = sBlockScan::instance()->getTxReceiptStatus($chainId, $txhash);
        }

        if(empty($getTxReceiptStatus['status'])){
            $message = '';
            if(!empty($getTxReceiptStatus['message'])){
                $message = ' '.$getTxReceiptStatus['message'];
            }
            return ['error' => 1, 'data' => sLanguage::instance()->sL('Transaction not found. Please update in a few minutes.').''.$message, 'typeError'=>'TransactionNotFound'];
        }

        if(empty($getTxReceiptStatus['result']['status'])){
            return ['error' => 1, 'data' => sLanguage::instance()->sL('Transaction status is empty. Please update in a few minutes.')];
        }

        if(sSettings::instance()->useWeb3){
            $TxHashData = sWeb3::instance()->getTransactionByHash($chainId, $txhash);
        } else {
            $TxHashData = sBlockScan::instance()->getTransactionByHash($chainId, $txhash);
        }

        if (empty($TxHashData['result']['input'])) {
            return ['error' => 1, 'data' => sLanguage::instance()->sL('Transaction not found. Please update in a few minutes.')];
        }

        $addressSender = $TxHashData['result']['from'];
        $addressContractPayData = $TxHashData['result']['to'];

        $InputData = $TxHashData['result']['input'];
        $ParametersArr = [
            ['type' => 'address', 'name' => 'addressRecipient'],
            ['type' => 'address', 'name' => 'addressRecipientToken'],
            ['type' => 'uint256', 'name' => 'amountRecipientToken'],
            ['type' => 'address', 'name' => 'addressSenderToken'],
            ['type' => 'uint256', 'name' => 'amountSenderToken'],
            ['type' => 'address', 'name' => 'addressSwapRouter'],
            ['type' => 'uint256', 'name' => 'amountOutMinCashback'],
            ['type' => 'string', 'name' => 'paymentDescription'],
        ];

        $decodeInputData = sAbi::instance()->decodeInputData($ParametersArr, $InputData);

        foreach ($ParametersArr as $ParameterRow) {
            if (empty($decodeInputData[$ParameterRow['name']]['data'])) {
                return ['error' => 1, 'data' => 'Empty Parameter: ' . $ParameterRow['name']];
            }
        }
        $decodeInputData['paymentDescription']['data'] = strstr($decodeInputData['paymentDescription']['data'], 'orderId:');

        $PaymentDescriptionArr = sW3pay::instance()->getPaymentInfoArrByStr($decodeInputData['paymentDescription']['data']);
        if (!empty($PaymentDescriptionArr['error'])) {
            return $PaymentDescriptionArr;
        }

        $orderId = $PaymentDescriptionArr['dataArr']['orderId'];
        $SignInContract = $PaymentDescriptionArr['dataArr']['Sign'];

        $verificationSignStr = sW3pay::instance()->generatePaymentInfo(
            $orderId,
            $chainId,
            $addressContractPayData,
            $decodeInputData['addressRecipient']['data'],
            $decodeInputData['addressRecipientToken']['data'],
            $decodeInputData['amountRecipientToken']['data'],
            $decodeInputData['addressSwapRouter']['data']
        );

        $verificationSignArr = sW3pay::instance()->getPaymentInfoArrByStr($verificationSignStr);
        if (!empty($verificationSignArr['error'])) {
            return $verificationSignArr;
        }

        $VerificationSign = $verificationSignArr['dataArr']['Sign'];

        $checkSign = [
            'chainId' => $chainId,
            'tx' => $txhash,
            'orderId' => $orderId,
            'addressSender' => $addressSender,
            'SignInContract' => $SignInContract,
            'VerificationSign' => $VerificationSign,
            'InputData' => $decodeInputData,
        ];

        // Add Info
        $CashbackData = sW3pay::instance()->getCashbackData($_GET['chainid'], $checkSign['addressSender']);
        $PaymentSettingsByChainId = $this->getPaymentSettingsByChainId($chainId);
        $payTokensArr=[];
        foreach ($PaymentSettingsByChainId['payTokens'] as $payTokenData){
            $payTokensArr[mb_strtolower($payTokenData['addressCoin'])] = $payTokenData;
        }

        // Add extData Tokens
        if(!empty($payTokensArr[mb_strtolower($checkSign['InputData']['addressRecipientToken']['data'])])){
            $checkSign['InputData']['addressRecipientToken']['extData'] = $payTokensArr[mb_strtolower($checkSign['InputData']['addressRecipientToken']['data'])];
            $checkSign['InputData']['amountRecipientToken']['extData'] = $this->wei2eth($checkSign['InputData']['amountRecipientToken']['data'], $checkSign['InputData']['addressRecipientToken']['extData']['decimals']);
        }
        if(!empty($payTokensArr[mb_strtolower($checkSign['InputData']['addressSenderToken']['data'])])){
            $checkSign['InputData']['addressSenderToken']['extData'] = $payTokensArr[mb_strtolower($checkSign['InputData']['addressSenderToken']['data'])];
            $checkSign['InputData']['amountSenderToken']['extData'] = $this->wei2eth($checkSign['InputData']['amountSenderToken']['data'], $checkSign['InputData']['addressSenderToken']['extData']['decimals']);
        }

        // return true
        if (base64_decode($SignInContract) === base64_decode($VerificationSign)) {
            sTransactions::instance()->saveTransaction($orderId, $chainId, $txhash, 1);
            return ['error' => 0, 'data' => 'Success', 'checkSign' => $checkSign, 'CashbackData' => $CashbackData, 'PaymentSettingsByChainId' => $PaymentSettingsByChainId];
        }

        // Check log
        $PaymentLog = sTransactions::instance()->getTransaction($orderId);
        $importantText='';
        if(!empty($PaymentLog['tx']) && $PaymentLog['chainId'] == $chainId && base64_decode(mb_strtolower($PaymentLog['tx'])) === base64_decode(mb_strtolower($txhash))){
            $importantText = sLanguage::instance()->sL('The system found a record of successful payment for this order dated').' '.$PaymentLog['date'].'. '.sLanguage::instance()->sL('The signature may not match if the Secret key has changed since the payment date. Manual verification required.');
        }

        return ['error' => 1, 'data' => sLanguage::instance()->sL('Signatures do not match'), 'checkSign' => $checkSign, 'CashbackData' => $CashbackData, 'PaymentSettingsByChainId' => $PaymentSettingsByChainId, 'PaymentLog' => $PaymentLog, 'importantText'=>$importantText, 'typeError'=>'SignaturFalse'];
    }

    /**
     * Input Data Hex to String
     * @param $hex
     * @return string
     */
    function hex2str($hex)
    {
        $str = '';
        for ($i = 0; $i < strlen($hex); $i += 2) $str .= chr(hexdec(substr($hex, $i, 2)));
        return $str;
    }

    /**
     * Int value is divisible up to decimal
     * @param $value
     * @param int $decimal
     * @return string|null
     */
    public function wei2eth($value, $decimal = 18)
    {
        $dividend = (string)$value;
        $divisor = (string)'1' . str_repeat('0', $decimal);
        return bcdiv($value, $divisor, $decimal);
    }

    /**
     * @param $value
     * @param int $decimal
     * @return string
     */
    public function eth2wei($value, $decimal = 18)
    {
        $divisor = (string)'1' . str_repeat('0', $decimal);
        return bcmul($value, $divisor);
    }

    /**
     * Generating a signature using a secret key
     * @param $dataSet
     * @return string
     */
    public function generateSign($dataSet)
    {
        ksort($dataSet, SORT_STRING); // sort array elements in alphabetical order
        array_push($dataSet, sSettings::instance()->SecretSignKey); // add "secret key" to the end of the array
        $signString = implode(':', $dataSet); // concatenate values ​​through the symbol ":"
        $sign = base64_encode(md5($signString, true)); // we take the MD5 hash in binary form from the generated string and encode it in BASE64
        return $sign;
    }

    //address addressRecipient, address addressStablecoinUSD, uint256 amountStablecoinUSD, address addressSwapRouter, uint amountOutMinCashback, string calldata paymentInfo

    /**
     * generate PaymentInfo - it includes signature and order id
     * @param $orderId
     * @param $chainId
     * @param $addressRecipient
     * @param $addressRecipientToken
     * @param $amountRecipientToken
     * @param $addressSwapRouter
     * @return string
     */
    public function generatePaymentInfo($orderId, $chainId, $addressContractPayData, $addressRecipient, $addressRecipientToken, $amountRecipientToken, $addressSwapRouter)
    {
        if (empty($orderId)) {
            return 'empty orderId';
        }
        if (empty($chainId)) {
            return 'empty chainId';
        }
        if (empty($addressRecipient)) {
            return 'empty addressRecipient';
        }
        if (empty($addressRecipientToken)) {
            return 'empty addressRecipientToken';
        }
        if (empty($amountRecipientToken)) {
            return 'empty amountRecipientToken';
        }
        if (empty($addressSwapRouter)) {
            return 'empty addressSwapRouter';
        }

        $dataSet = [];
        $dataSet['orderId'] = (int)$orderId;
        $dataSet['chainId'] = (int)$chainId;
        $dataSet['addressContractPayData'] = mb_strtolower($addressContractPayData);
        $dataSet['addressRecipient'] = mb_strtolower($addressRecipient);
        $dataSet['addressRecipientToken'] = mb_strtolower($addressRecipientToken);
        $dataSet['amountRecipientToken'] = (int)$amountRecipientToken;
        $dataSet['addressSwapRouter'] = mb_strtolower($addressSwapRouter);

        $PaymentInfo = sW3pay::instance()->getPaymentInfoStrByArr($orderId, $this->generateSign($dataSet));

        return $PaymentInfo;
    }

    /**
     * get PaymentInfo String By Array
     * @param $orderId
     * @param $Sign
     * @return string
     */
    public function getPaymentInfoStrByArr($orderId, $Sign)
    {
        return 'orderId:' . $orderId . ';Sign:' . $Sign;
    }

    /**
     * get PaymentInfo Array By String
     * @param $PaymentDescription
     * @return array
     */
    public function getPaymentInfoArrByStr($PaymentDescription)
    {

        $ParametersDesc = explode(';', $PaymentDescription);
        $orderId = '';
        $Sign = '';
        if (!empty($ParametersDesc[1])) {
            $orderId = str_replace("orderId:", "", strstr($ParametersDesc[0], 'orderId:'));
            $Sign = str_replace("Sign:", "", strstr($ParametersDesc[1], 'Sign:'));;
        }

        if (empty($orderId)) {
            return ['error' => 1, 'data' => 'Empty orderId'];
        }
        if (empty($Sign)) {
            return ['error' => 1, 'data' => 'Empty Sign'];
        }

        $PaymentDescriptionArr = ['orderId' => $orderId, 'Sign' => $Sign];
        return ['error' => 0, 'data' => 'Success', 'dataArr' => $PaymentDescriptionArr];
    }


}