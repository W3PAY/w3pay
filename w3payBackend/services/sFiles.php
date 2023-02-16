<?php
/**
 * W3PAY - Web3 Crypto Payments
 * Website: https://w3pay.dev
 * GitHub Website: https://w3pay.github.io/
 * GitHub: https://github.com/w3pay
 * Copyright (c)
 */

class sFiles
{

    protected static $instance;

    /**
     * @return sFiles
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public $modeDir = 0755;
    public $modeFile = 0644;

    /**
     * @param $dir
     * @return bool
     */
    public function createDir($dir)
    {
        try {
            if (!is_dir($dir)) {
                if (!@mkdir($dir, $this->modeDir, true)) {
                    return false;
                }
            }
        } catch(Exception $e) {
            return false;
        }
        if(!file_exists($dir)){
            return false;
        }
        return true;
    }

    /**
     * @param $filename
     * @param $text
     * @param bool $createDir
     * @return bool
     */
    public function createFile($filename, $text, $createDir=true)
    {
        try {
            if($createDir){ if(!$this->createDir(dirname($filename))){ return false; } }
            $fp = @fopen($filename, "w");
            @fwrite($fp, $text);
            @fclose($fp);
        } catch(Exception $e) {
            return false;
        }
        if(!file_exists($filename)){
            return false;
        }
        return true;
    }

    /**
     * @param $RemoteDir
     * @param $dir
     * @return bool
     */
    public function copyFile($RemoteDir, $dir){
        try {
            if(!@copy($RemoteDir, $dir)){
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * @param $dir
     * @return bool
     */
    public function removeDir($dir){
        if(file_exists($dir)){
            $this->delDir($dir);
        }
        return true;
    }

    /**
     * @param $dir
     * @return bool
     */
    private function delDir($dir) {
        $files = array_diff(scandir($dir), ['.','..']);
        foreach ($files as $file) {
            (is_dir($dir.'/'.$file)) ? $this->delDir($dir.'/'.$file) : unlink($dir.'/'.$file);
        }
        return rmdir($dir);
    }

}