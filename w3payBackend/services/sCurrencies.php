<?php
/**
 * W3PAY - Web3 Crypto Payments
 * Website: https://w3pay.dev
 * GitHub Website: https://w3pay.github.io/
 * GitHub: https://github.com/w3pay
 * Copyright (c)
 */

include_once(__DIR__.'/sFiles.php');

class sCurrencies
{

    protected static $instance;

    /**
     * @return sCurrencies
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public $CmcApiKey = '';
    public $rememberFiat = 0;
    public $CmcIdUsd = '2781';
    public $fileCmcFiatRates = __DIR__.'/../settings/CmcFiatRates.json';

    public function setApiKey($ApiKey=''){
        if(!empty($ApiKey)){
            $this->CmcApiKey = $ApiKey;
            return ['error' => 0, 'data' => 'Success', 'ApiKey'=>$this->CmcApiKey];
        } else {
            if(file_exists(__DIR__.'/../settings/sSettings.php')){
                include_once(__DIR__.'/../settings/sSettings.php');
            }
            if (!class_exists('sSettings')) {
                return ['error' => 1, 'data' => 'Error: sSettings class not found'];
            }
            $this->CmcApiKey = sSettings::instance()->CmcApi;
            $this->rememberFiat = (int)sSettings::instance()->rememberFiat;
        }
        return ['error' => 0, 'data' => 'Success', 'ApiKey'=>$this->CmcApiKey];
    }

    public function getFiatPrice($giveFiatSymbol, $giveAmount, $getFiatSymbol){
        $giveAmount = floatval($giveAmount);

        $CmcFiatArr = $this->getCmcFiatArr();

        if(empty($giveAmount)){
            return ['error' => 1, 'data' => 'Error: Fiat giveAmount is empty.'];
        }
        if(empty($CmcFiatArr[$giveFiatSymbol])){
            return ['error' => 1, 'data' => 'Error: Fiat '.$giveFiatSymbol.' not found.'];
        }
        if(empty($CmcFiatArr[$getFiatSymbol])){
            return ['error' => 1, 'data' => 'Error: Fiat '.$getFiatSymbol.' not found.'];
        }

        $CmcFiatRatesArr = $this->getCmcFiatRatesArr();
        if(!empty($CmcFiatRatesArr['error'])){
            return $CmcFiatRatesArr;
        }
        if(empty($CmcFiatRatesArr['CmcFiatArr']['USD']['USD'])){
            return ['error' => 1, 'data' => 'Error: FiatRatesInUSD USD rate not found.'];
        }
        if(empty($CmcFiatRatesArr['CmcFiatArr'][$giveFiatSymbol]['USD'])){
            return ['error' => 1, 'data' => 'Error: FiatRatesInUSD '.$giveFiatSymbol.' rate not found.'];
        }
        if(empty($CmcFiatRatesArr['CmcFiatArr'][$getFiatSymbol]['USD'])){
            return ['error' => 1, 'data' => 'Error: FiatRatesInUSD '.$getFiatSymbol.' rate not found.'];
        }

        $giveFiatPrice = floatval($CmcFiatRatesArr['CmcFiatArr'][$giveFiatSymbol]['USD']);
        $getFiatPrice = floatval($CmcFiatRatesArr['CmcFiatArr'][$getFiatSymbol]['USD']);
        $getAmount = ($giveFiatPrice*$giveAmount)/$getFiatPrice;

        return ['error' => 0, 'data' => 'Success', 'amount' => $getAmount];
    }

    public function getCmcFiatRatesArr(){
        $ApiKey = $this->setApiKey();
        if(!empty($ApiKey['error'])){
            return $ApiKey;
        }

        $date = date("Y-m-d H:i:s");
        $rememberDate = date('Y-m-d H:i:s', strtotime($date. ' - '.$this->rememberFiat.' hours'));
        if (file_exists($this->fileCmcFiatRates)) {
            $modifiedDate = date("Y-m-d H:i:s", filemtime($this->fileCmcFiatRates));

            if($modifiedDate > $rememberDate){
                return ['error' => 0, 'data' => 'Success', 'CmcFiatArr'=>json_decode(file_get_contents($this->fileCmcFiatRates), true)];
            }
        }

        $Rates_ob_start = $this->getFiatRatesInUSD_ob_start();
        if (strpos($Rates_ob_start, 'Caught exception:') !== false) {
            return ['error' => 1, 'data' => 'Error: CurrencyPrice.'.$Rates_ob_start];
        }
        $RatesArr = json_decode($Rates_ob_start, true);
        if(empty($RatesArr['data'][$this->CmcIdUsd]['quote'][$this->CmcIdUsd]['price'])){
            return ['error' => 1, 'data' => 'Error: FiatRatesInUSD USD rate not found.'];
        }

        $CmcFiatArr = $this->getCmcFiatArr();
        foreach ($CmcFiatArr as $CmcFiatKey => $CmcFiatRow){
            if(!empty($RatesArr['data'][$CmcFiatRow['id']]['quote'][$this->CmcIdUsd]['price'])){
                $CmcFiatArr[$CmcFiatKey]['USD'] = $RatesArr['data'][$CmcFiatRow['id']]['quote'][$this->CmcIdUsd]['price'];
            }
        }

        // Save file
        sFiles::instance()->createFile($this->fileCmcFiatRates, json_encode($CmcFiatArr));

        return ['error' => 0, 'data' => 'Success', 'CmcFiatArr'=>$CmcFiatArr];
    }

    public function getFiatRatesInUSD_ob_start(){
        if(empty($this->CmcApiKey)){
            return 'Caught exception: Api key is empty';
        }
        $CmcFiatArr = $this->getCmcFiatArr();
        $ids = [];
        foreach ($CmcFiatArr as $CmcFiatRow){
            $ids[] = $CmcFiatRow['id'];
        }
        ob_start();
        try {
            // https://coinmarketcap.com/api/documentation/v1/#section/Standards-and-Conventions
            // https://web-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest?symbol=USDT&convert_id=2806
            $url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest';
            $parameters = [
                'id' => implode(',', $ids),
                'convert_id' => $this->CmcIdUsd, // id USD
            ];
            $headers = [
                'Accepts: application/json',
                'X-CMC_PRO_API_KEY: '.$this->CmcApiKey,
            ];
            $qs = http_build_query($parameters); // query string encode the parameters
            $request = "{$url}?{$qs}"; // create the request URL
            $curl = curl_init(); // Get cURL resource
            // Set cURL options
            curl_setopt_array($curl, array(
                CURLOPT_URL => $request,            // set the request URL
                CURLOPT_HTTPHEADER => $headers,     // set the headers
                CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
            ));
            $response = curl_exec($curl); // Send the request, save the response
            echo $response;
            curl_close($curl); // Close request
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }

        $contents = ob_get_contents();             //  Instead, output above is saved to $contents
        ob_end_clean();
        return $contents;
    }

    public function checkApi($ApiKey){
        $this->setApiKey($ApiKey);
        $Rates_ob_start = $this->getFiatRatesInUSD_ob_start();
        if (strpos($Rates_ob_start, 'Caught exception:') !== false) {
            return ['error' => 1, 'data' => 'Error: CoinMarketCap '.$Rates_ob_start];
        }
        $RatesArr = json_decode($Rates_ob_start, true);
        if(empty($RatesArr['data'][$this->CmcIdUsd]['quote'][$this->CmcIdUsd]['price'])){
            return ['error' => 1, 'data' => 'Error: CoinMarketCap API'];
        }
        return ['error' => 0, 'data' => 'Success'];
    }

    public function getCmcFiatArr(){
        $CmcFiatArr = [
            'USD' => ['id' => 2781],
            'AUD' => ['id' => 2782],
            'BRL' => ['id' => 2783],
            'CAD' => ['id' => 2784],
            'CHF' => ['id' => 2785],
            'CLP' => ['id' => 2786],
            'CNY' => ['id' => 2787],
            'CZK' => ['id' => 2788],
            'DKK' => ['id' => 2789],
            'EUR' => ['id' => 2790],
            'GBP' => ['id' => 2791],
            'HKD' => ['id' => 2792],
            'HUF' => ['id' => 2793],
            'IDR' => ['id' => 2794],
            'ILS' => ['id' => 2795],
            'INR' => ['id' => 2796],
            'JPY' => ['id' => 2797],
            'KRW' => ['id' => 2798],
            'MXN' => ['id' => 2799],
            'MYR' => ['id' => 2800],
            'NOK' => ['id' => 2801],
            'NZD' => ['id' => 2802],
            'PHP' => ['id' => 2803],
            'PKR' => ['id' => 2804],
            'PLN' => ['id' => 2805],
            'RUB' => ['id' => 2806],
            'SEK' => ['id' => 2807],
            'SGD' => ['id' => 2808],
            'THB' => ['id' => 2809],
            'TRY' => ['id' => 2810],
            'TWD' => ['id' => 2811],
            'ZAR' => ['id' => 2812],
            'AED' => ['id' => 2813],
            'BGN' => ['id' => 2814],
            'HRK' => ['id' => 2815],
            'MUR' => ['id' => 2816],
            'RON' => ['id' => 2817],
            'ISK' => ['id' => 2818],
            'NGN' => ['id' => 2819],
            'COP' => ['id' => 2820],
            'ARS' => ['id' => 2821],
            'PEN' => ['id' => 2822],
            'VND' => ['id' => 2823],
            'UAH' => ['id' => 2824],
            'BOB' => ['id' => 2832],
            'ALL' => ['id' => 3526],
            'AMD' => ['id' => 3527],
            'AZN' => ['id' => 3528],
            'BAM' => ['id' => 3529],
            'BDT' => ['id' => 3530],
            'BHD' => ['id' => 3531],
            'BMD' => ['id' => 3532],
            'BYN' => ['id' => 3533],
            'CRC' => ['id' => 3534],
            'CUP' => ['id' => 3535],
            'DOP' => ['id' => 3536],
            'DZD' => ['id' => 3537],
            'EGP' => ['id' => 3538],
            'GEL' => ['id' => 3539],
            'GHS' => ['id' => 3540],
            'GTQ' => ['id' => 3541],
            'HNL' => ['id' => 3542],
            'IQD' => ['id' => 3543],
            'IRR' => ['id' => 3544],
            'JMD' => ['id' => 3545],
            'JOD' => ['id' => 3546],
            'KES' => ['id' => 3547],
            'KGS' => ['id' => 3548],
            'KHR' => ['id' => 3549],
            'KWD' => ['id' => 3550],
            'KZT' => ['id' => 3551],
            'LBP' => ['id' => 3552],
            'LKR' => ['id' => 3553],
            'MAD' => ['id' => 3554],
            'MDL' => ['id' => 3555],
            'MKD' => ['id' => 3556],
            'MMK' => ['id' => 3557],
            'MNT' => ['id' => 3558],
            'NAD' => ['id' => 3559],
            'NIO' => ['id' => 3560],
            'NPR' => ['id' => 3561],
            'OMR' => ['id' => 3562],
            'PAB' => ['id' => 3563],
            'QAR' => ['id' => 3564],
            'RSD' => ['id' => 3565],
            'SAR' => ['id' => 3566],
            'SSP' => ['id' => 3567],
            'TND' => ['id' => 3568],
            'TTD' => ['id' => 3569],
            'UGX' => ['id' => 3570],
            'UYU' => ['id' => 3571],
            'UZS' => ['id' => 3572],
            'VES' => ['id' => 3573],

            'BTC' => ['id' => 1],
            'ETH' => ['id' => 1027],
            'BNB' => ['id' => 1839],
            'MATIC' => ['id' => 3890],
            'AVAX' => ['id' => 5805],
            'FTM' => ['id' => 3513],
        ];
        return $CmcFiatArr;
    }

}