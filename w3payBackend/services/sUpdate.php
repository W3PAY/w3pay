<?php
/**
 * W3PAY - Web3 Crypto Payments
 * Website: https://w3pay.dev
 * GitHub Website: https://w3pay.github.io/
 * GitHub: https://github.com/w3pay
 * Copyright (c)
 */

include_once(__DIR__.'/../services/sFiles.php');

class sUpdate
{

    protected static $instance;

    /**
     * @return sUpdate
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public $versionJson = __DIR__.'/../version.json';
    public $dirUpdates = __DIR__.'/../updates';
    public $dirUpdateTemp = __DIR__.'/../updates/temp';

    /**
     * @return array
     */
    public function getUpdatesArr(){
        $UpdatesArr = [];
        $UpdatesArr['none'] = [
            'nameCms' => 'CMS None',
            'url' => 'https://w3pay.github.io/plugins/w3pay/w3pay.zip',
            'rootFolderZip' => '',
            'modulePath' => __DIR__.'/../../../w3pay',
        ];
        $UpdatesArr['wp'] = [
            'nameCms' => 'CMS WordPress WooCommerce',
            'url' => 'https://w3pay.github.io/plugins/w3payWordPress/w3pay.zip',
            'rootFolderZip' => '',
            'modulePath' => __DIR__.'/../../../../../../wp-content/plugins/w3pay',
        ];
        $UpdatesArr['oc'] = [
            'nameCms' => 'CMS OpenCart',
            'url' => 'https://w3pay.github.io/plugins/w3payOpenCart/w3pay.ocmod.zip',
            'rootFolderZip' => '',
            'modulePath' => __DIR__.'/../../../../../extension/w3pay',
        ];
        return $UpdatesArr;
    }

    /**
     * @param string $cms
     * @return array
     */
    public function startUpdate($cms='none'){
        $w3payFrontendPath = sDefines::instance()->getPaths()['w3payFrontendPath'];
        $w3payBackend = sDefines::instance()->getPaths()['w3payBackend'];

        $destinationsArr = [
            'w3payBackend' => $w3payBackend,
            'w3payFrontend' => $w3payFrontendPath,
        ];

        $UpdatesArr = $this->getUpdatesArr();
        if(empty($UpdatesArr[$cms])){
            return ['error' => 1, 'data' => 'Update failed: CMS '.$cms.' data update not found.'];
        }
        $UpdateArr = $UpdatesArr[$cms];

        $fileZIP = $this->dirUpdates.'/update.zip';

        if (!sFiles::instance()->createDir($this->dirUpdates)) {
            return ['error' => 1, 'data' => 'Update failed: Dir: '.$this->dirUpdates.' not found.'];
        }

        if(!file_exists($UpdateArr['modulePath'])){
            return ['error' => 1, 'data' => 'Update failed: '.$UpdateArr['nameCms'].' Dir: '.$UpdateArr['modulePath'].' not found'];
        }

        if(!sFiles::instance()->copyFile($UpdateArr['url'], $fileZIP)){
            return ['error' => 1, 'data' => 'Update failed: '.$UpdateArr['url'].' copy failed.'];
        }

        if (!file_exists($fileZIP)) {
            return ['error' => 1, 'data' => 'Update failed: file '.$fileZIP.' not found.'];
        }

        $PathsArr = [];
        $zip = new \ZipArchive();

        if ($zip->open($fileZIP) === true) {

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $FileInfo = $zip->statIndex($i);

                    // Check version.json
                    if (strpos($FileInfo['name'], 'version.json') !== false)
                    {
                        $versionInfoStr = $zip->getFromIndex($FileInfo['index']);
                        $versionInfoArr = json_decode($versionInfoStr, true);
                    }

                    $tempPath = str_replace('\\', '/', $this->dirUpdateTemp.'/'.$FileInfo['name']);
                    $tempPath = str_replace('//', '/', $tempPath);

                    $needPath =  $UpdateArr['modulePath'].'/'.str_replace($UpdateArr['rootFolderZip'], '', strstr($FileInfo['name'], $UpdateArr['rootFolderZip']));
                    foreach ($destinationsArr as $destinationKey => $destination){
                        if (strpos($FileInfo['name'], $destinationKey) !== false) {
                            $needPath =  str_replace($UpdateArr['rootFolderZip'], '', strstr($FileInfo['name'], $UpdateArr['rootFolderZip']));
                            $needPath =  $destination.str_replace($destinationKey, '', strstr($needPath, $destinationKey));
                        }
                    }
                    $needPath = str_replace('\\', '/', $needPath);
                    $needPath = str_replace('//', '/', $needPath);

                    $status = 'n';
                    if (file_exists($needPath)) {
                        $status = 'y';
                    }

                    $PathsArr[] = [
                        'tempPath' => $tempPath,
                        'needPath' => $needPath,
                        'path' => $FileInfo['name'],
                        'status' => $status,
                    ];
                }

                $VersionData = $this->getVersionData();
                if(!empty($VersionData['version']) && !empty($versionInfoArr['version']) && $VersionData['version']===$versionInfoArr['version']){
                    return ['error' => 0, 'data' => 'Success. The latest version has been successfully installed.'];
                }

            if (!sFiles::instance()->createDir($this->dirUpdateTemp)) {
                return ['error' => 1, 'data' => 'Update failed: Dir: '.$this->dirUpdateTemp.' not found.'];
            }

            $zip->extractTo($this->dirUpdateTemp);

            if(!empty($PathsArr)){
                foreach ($PathsArr as $PathData){
                    if(file_exists($PathData['tempPath'])){
                        if(is_file($PathData['tempPath'])){
                            $dir = dirname($PathData['needPath']);
                            if (!sFiles::instance()->createDir($dir)) {
                                return ['error' => 1, 'data' => 'Update: Dir: '.$dir.' not found.'];
                            }

                            if(!@rename($PathData['tempPath'], $PathData['needPath'])){
                                return ['error' => 1, 'data' => 'Update: rename: '.$PathData['tempPath'].' -> '.$PathData['needPath'].' not found.'];
                            }
                        }
                    }
                }
            }

            $zip->close();

            if(!sFiles::instance()->removeDir($this->dirUpdateTemp)){
                return ['error' => 1, 'data' => 'Update failed: removeDir: '.$this->dirUpdateTemp.' error.'];
            }
            if(!sFiles::instance()->removeDir($this->dirUpdates)){
                return ['error' => 1, 'data' => 'Update failed: removeDir: '.$this->dirUpdates.' error.'];
            }
        } else {
            return ['error' => 1, 'data' => 'Update failed: file '.$fileZIP.' not found.'];
        }
        return ['error' => 0, 'data' => 'Success. You have installed the latest version.'];
    }

    /**
     * @return array|mixed
     */
    public function getVersionData(){
        $VersionData=[];
        if(file_exists($this->versionJson)){
            $VersionData = json_decode(file_get_contents($this->versionJson), true);
        }
        return $VersionData;
    }

}