<?php
/**
 * W3PAY - Web3 Crypto Payments
 * Website: https://w3pay.dev
 * GitHub Website: https://w3pay.github.io/
 * GitHub: https://github.com/w3pay
 * Copyright (c)
 */

class sTransactions
{

    protected static $instance;

    /**
     * @return sTransactions
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public $paymentsLogPath = __DIR__.'/../paymentsLog';
    public $PaymentLogPageI = 100;

    /**
     * @return array
     */
    public function getStatusArr(){
        $StatusArr=[];
        $StatusArr[1] = 'Completed';
        $StatusArr[2] = 'Return';
        return $StatusArr;
    }

    /**
     * @param $orderId
     * @param $chainId
     * @param $tx
     * @param $status
     * @return array
     */
    public function saveTransaction($orderId, $chainId, $tx, $status){
        $orderId = (int)$orderId;
        $dir = __DIR__.'/../paymentsLog';
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0755, true)) {
                return ['error' => 1, 'data' => 'Dir: '.$dir.' not found.'];
            }
        }
        $page = ceil($orderId/$this->PaymentLogPageI);

        $PaymentsLogArr = $this->getTransactionsPageArr($page);
        if(empty($PaymentsLogArr[$orderId])){
            $date = gmdate('Y-m-d H:i:s');
            $PaymentsLogArr[$orderId] = [$orderId, $chainId, $tx, $status, $date];
            $this->saveTransactionsArr($PaymentsLogArr, $page);
        }

        return ['error' => 0, 'data' => 'Success'];
    }

    /**
     * @param $orderId
     * @return array|mixed
     */
    public function getTransaction($orderId){
        $orderId = (int)$orderId;
        $page = ceil($orderId/$this->PaymentLogPageI);
        $PaymentsLogArr = $this->getTransactionsPageArr($page);
        if(!empty($PaymentsLogArr[$orderId])){
            return $PaymentsLogArr[$orderId];
        }
        return [];
    }

    /**
     * @param $PaymentsLogArr
     * @param $page
     * @return array
     */
    public function saveTransactionsArr($PaymentsLogArr, $page){
        $dir = __DIR__.'/../paymentsLog';
        $fileName = $dir.'/p'.$page.'.php';

        $LogsText = '<?php /*'.PHP_EOL;

        ksort($PaymentsLogArr);

        foreach ($PaymentsLogArr as $PaymentsLogRow){
            $LogsText .= implode(';', $PaymentsLogRow).PHP_EOL;
        }

        file_put_contents($fileName, $LogsText);

        return ['error' => 0, 'data' => 'Success'];
    }

    /**
     * @param $page
     * @return array
     */
    public function getTransactionsPageArr($page){
        $dir = $this->paymentsLogPath;
        $fileName = $dir.'/p'.$page.'.php';

        $PaymentsLogArr=[];
        if (!file_exists($fileName)) {
            return $PaymentsLogArr;
        }

        $LogsText = file_get_contents($fileName);
        $LogsText = str_replace('<?php /*'.PHP_EOL, "", $LogsText);
        //echo $LogsText;

        $csvRows = explode(PHP_EOL, $LogsText);
        foreach ($csvRows as $csvRow){
            $csvVals = explode(';', $csvRow);
            if(!empty($csvVals[4])){
                $PaymentsLogArr[$csvVals[0]]['orderId'] = $csvVals[0];
                $PaymentsLogArr[$csvVals[0]]['chainId'] = $csvVals[1];
                $PaymentsLogArr[$csvVals[0]]['tx'] = $csvVals[2];
                $PaymentsLogArr[$csvVals[0]]['status'] = $csvVals[3];
                $PaymentsLogArr[$csvVals[0]]['date'] = $csvVals[4];
            }
        }
        ksort($PaymentsLogArr);

        return $PaymentsLogArr;
    }

    /**
     * @return array
     */
    public function getTransactionsPages(){
        $TransactionsPages=[];
        $TransactionsPages['txPageData']=[];
        $dir = $this->paymentsLogPath;
        $tx_page = 0;
        if(!empty($_GET['tx_page'])){
            $tx_page = $_GET['tx_page'];
        }
        if(file_exists($dir)) {
            $filesArr = scandir($dir);
            foreach ($filesArr as $fileName){
                if($fileName!='.' && $fileName!='..'){
                    $page = str_replace(".php", "", $fileName);
                    $page = str_replace("p", "", $page);
                    if(empty($tx_page)){
                        $tx_page = $page;
                    }

                    $TransactionsPages['txPageData'][$page]=[
                        'path' => $dir.'/'.$fileName,
                        'page' => $page,
                        'order_start' => $page,
                        'order_finish' => $page*$this->PaymentLogPageI,
                    ];
                }
            }
        }
        ksort($TransactionsPages['txPageData']);
        $TransactionsPages['tx_page'] = $tx_page;

        return $TransactionsPages;
    }

}