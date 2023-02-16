<?php
/**
 * W3PAY - Web3 Crypto Payments
 * Website: https://w3pay.dev
 * GitHub Website: https://w3pay.github.io/
 * GitHub: https://github.com/w3pay
 * Copyright (c)
 */

class sAbi
{

    protected static $instance;

    /**
     * @return sAbi
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @param $ParametersArr - [['type'=>'address', 'name'=>'addressName'],['type'=>'uint256', 'name'=>'amountName']];
     * @param $InputData
     * @return array
     */
    public function decodeInputData($ParametersArr, $InputData){
        $decodeInputData=[];

        $InputDataWithoutMethodId = substr($InputData,  10);

        $offset=0;
        foreach ($ParametersArr as $Parameter){
            $Size = $this->getSize($Parameter['type']);
            $ParameterData= $this->getParameterData($Parameter['type'], $InputDataWithoutMethodId, $offset);
            $offset += $Size;

            $Parameter['type'];
            $Parameter['name'];
            $decodeInputData[$Parameter['name']]=['type'=>$Parameter['type'],'name'=>$Parameter['name'],'data'=>$ParameterData];
        }

        return $decodeInputData;
    }

    /**
     * @param $ParameterType
     * @param $InputDataWithoutMethodId
     * @param $offset
     * @return string|string[]
     */
    public function getParameterData($ParameterType, $InputDataWithoutMethodId, $offset){
        $Size = $this->getSize($ParameterType);

        if($ParameterType=='address'){
            $ParameterData = mb_substr($InputDataWithoutMethodId,$offset,$Size);
            $ParameterData = str_replace("000000000000000000000000", "0x", $ParameterData);
        }
        if($ParameterType=='uint256'){
            $ParameterData = mb_substr($InputDataWithoutMethodId,$offset,$Size);
            $ParameterData = $this->bchexdec($ParameterData);
        }
        if($ParameterType=='string'){
            $ParameterData = mb_substr($InputDataWithoutMethodId,$offset);
            $ParameterData = $this->hex2str($ParameterData);
        }

        return $ParameterData;
    }

    /**
     * @param $ParameterType
     * @return int
     */
    public function getSize($ParameterType){
        $Size=0;
        if($ParameterType=='address'){
            $Size=64;
        }
        if($ParameterType=='uint256'){
            $Size=64;
        }
        if($ParameterType=='string'){
            // TODO dynamic types
        }
        return $Size;
    }

    /**
     * @param $hex
     * @return string
     */
    function hex2str($hex) {
        $str = '';
        for($i=0;$i<strlen($hex);$i+=2) $str .= chr(hexdec(substr($hex,$i,2)));
        return $str;
    }

    /**
     * @param $hex
     * @return string
     */
    public function bchexdec($hex)
    {
        $dec='';
        $len = strlen($hex);
        for ($i = 1; $i <= $len; $i++)$dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
        return $dec;
    }

}