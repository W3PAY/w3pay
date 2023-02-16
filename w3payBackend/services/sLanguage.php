<?php
/**
 * W3PAY - Web3 Crypto Payments
 * Website: https://w3pay.dev
 * GitHub Website: https://w3pay.github.io/
 * GitHub: https://github.com/w3pay
 * Copyright (c)
 */

include_once(__DIR__.'/../services/sDefines.php');

class sLanguage
{

    /**
     * sLanguage constructor.
     */
    function __construct() {
        sDefines::instance()->checkDefines();
    }

    protected static $instance;

    /**
     * @return sLanguage
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public $LangArr=[];
    public $languagesData=[];
    public $LangsArr=[];


    /**
     * @return string
     */
    public function getLanguagesPath(){
        return $_SERVER['DOCUMENT_ROOT'].'/'._W3PAY_w3payFrontend_.'/files/languages';
    }

    /**
     * @return array|mixed
     */
    public function getLanguagesData(){
        if(!empty($this->languagesData)){
            return $this->languagesData;
        }
        $languagesData=[];
        $langFile = $this->getLanguagesPath().'/languages.json';
        if(!file_exists($langFile)){
            return $languagesData;
        }
        $languagesData = json_decode(file_get_contents($langFile), true);
        $this->languagesData = $languagesData;
        return $languagesData;
    }

    /**
     * @return false|mixed|string
     */
    public function getCurrentLang(){
        $languagesData = sLanguage::instance()->getLanguagesData();
        // Check GET
        if(!empty($_REQUEST['lang'])){
            return $_REQUEST['lang'];
        }
        // Check Browser
        if(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
            $ACCEPT_LANGUAGE = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            foreach ($languagesData as $languageData){
                if($languageData['ISOCode']==mb_strtolower($ACCEPT_LANGUAGE)){
                    return $ACCEPT_LANGUAGE;
                }
            }
        }
        // Default
        return 'en';
    }

    /**
     * @param string $lang
     * @return array|mixed
     */
    public function getLangArr($lang=''){
        if(empty($lang)){ $lang=sLanguage::instance()->getCurrentLang(); }

        if(!empty($this->LangsArr[$lang])){
            return $this->LangsArr[$lang];
        }

        $LangArr=[];
        $langFile = $this->getLanguagesPath().'/'.$lang.'.json';
        if(file_exists($langFile)){
            $LangArr = json_decode(file_get_contents($langFile), true);
        }
        $this->LangArr = $LangArr;
        $this->LangsArr[$lang] = $LangArr;
        return $LangArr;
    }

    /**
     * @param $lText
     * @return mixed
     */
    public function sL($lText){
        $LangArr = $this->LangArr;
        if(!empty($LangArr[$lText])){
            return $LangArr[$lText];
        }
        return $lText;
    }

    /**
     * @return array
     */
    public function getLanguagesLinksArr(){
        $LanguagesArr=[];
        $lang=sLanguage::instance()->getCurrentLang();
        $languagesData = sLanguage::instance()->getLanguagesData();
        $REQUEST_URI = (!empty($_SERVER['REQUEST_URI']))?$_SERVER['REQUEST_URI']:'/';

        if(!empty($languagesData)){
            foreach ($languagesData as $languageData){
                $REQUEST_URI = str_replace("&lang=".$languageData['ISOCode'], "", $REQUEST_URI);
                $REQUEST_URI = str_replace("?lang=".$languageData['ISOCode'], "", $REQUEST_URI);
            }
            foreach ($languagesData as $languageKey => $languageData){
                $ParameterSymbol = (strpos($REQUEST_URI, '?') === false)?'?':'&';
                $LanguagesArr[$languageData['ISOCode']] = $languageData;
                $LanguagesArr[$languageData['ISOCode']]['link'] = $REQUEST_URI.$ParameterSymbol.'lang='.$languageData['ISOCode'];
                $LanguagesArr[$languageData['ISOCode']]['active'] = ($lang==$languageData['ISOCode'])?1:0;
            }
        }

        return $LanguagesArr;
    }

    /**
     * @return array
     */
    public function getLanguageBlock(){
        $data=[];
        $data['LangArr'] = sLanguage::instance()->getLangArr();
        $data['L'] = sLanguage::instance();
        $data['LanguagesLinksArr'] = sLanguage::instance()->getLanguagesLinksArr();
        $data['activeLanguage']=$data['LanguagesLinksArr']['en'];
        foreach ($data['LanguagesLinksArr'] as $LanguagesLinkData){
            if(!empty($LanguagesLinkData['active'])){
                $data['activeLanguage'] = $LanguagesLinkData;
            }
        }

        return [
            'error' => 0,
            'L' => $data['L'],
            'LangArr' => $data['LangArr'],
            'LanguagesLinksArr' => $data['LanguagesLinksArr'],
            'activeLanguage' => $data['activeLanguage'],
            'html'=>$this->getTemplateHtml(__DIR__.'/../widget/templates/language.php', $data)
        ];
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