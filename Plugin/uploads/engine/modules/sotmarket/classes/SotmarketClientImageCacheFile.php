<?php
/**
 * @ver 0.4
 * @author Андрей Смирнов
 **/

class SotmarketClientImageCacheFile extends SotmarketClientCacheFile
{
    private $sDefaultImagePath = '';

    public function __construct($config)
    {
        if (isset($config['defaultImgPath'])) {
            $this->sDefaultImagePath = $config['defaultImgPath'];
        } else {
            $this->sDefaultImagePath = '../default.jpg';
        }
        parent::__construct($config);
    }

    /**
     * @var string $sHash хэш кэша
     * @var mixed $sResult переменная в которой возвращаются данные из кэша
     * @var boolean $bDontTransform не сериализировать данные
     * @return boolean true если удалось прочитать данные из кэша
     **/
/*
    public function vOutputCache($sHash)
    {
        if (!$this->bCheckCache($sHash)) {
            $this->vOutputDefaultImage();
        }
        $sFileName = $this->sTmpPath . $sHash;
        $sContent = file_get_contents($sFileName);
        try {
            $aResult = unserialize($sContent);
            header('Content-type:' . $aResult['content-type']);
            echo $aResult['content'];
        } catch (Exception $e) {
            $this->vOutputDefaultImage();
        }
        return true;
    }
*/
    /**
     * Возвращает расширение с точкой
     **/
    public static function sGetExtensionWithDot($sFileName)
    {
        return strrchr($sFileName, '.');
    }

    /**
     *
     * @return true if success
     **/
    public function bSaveRemote($sHash, $sRemoteUrl)
    {
        if (empty($sRemoteUrl)) return;
        $oCurl = curl_init();
        curl_setopt($oCurl, CURLOPT_URL, $sRemoteUrl);
        curl_setopt($oCurl, CURLOPT_USERAGENT, 'sotmarket_dle_imagecache');
        curl_setopt($oCurl, CURLOPT_HEADER, 0);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 4);
        $sContent = curl_exec($oCurl);
        if (empty($sContent)) return false;
        $this->vSaveCache($sHash, $sContent, true);
        return true;
    }

    function sGetImagePath($sPostfix)
    {
        return '/' . $this->sTmpPath . $sPostfix;
    }

    function sGetDefaultImagePath()
    {
        return '/' . $this->sTmpPath . $this->sDefaultImagePath;
    }
}
