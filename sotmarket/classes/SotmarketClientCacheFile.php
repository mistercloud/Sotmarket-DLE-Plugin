<?php
/**
 * @ver 0.3
 * @author ������ �������
 **/

class SotmarketClientCacheFile extends SotmarketClientCache
{
    // @var string $sTmPath
    // ���� � ���������� � ���������� �������
    public $sTmpPath = null;

    public function __construct($config)
    {
        if (!empty($config['tmpPath'])) {
            $this->sTmpPath = $config['tmpPath'];
            parent::__construct($config);
        }
    }

    /**
     * @var string $sHash ��� ����
     * @var mixed $sResult ���������� � ������� ������������ ������ �� ����
     * @var boolean $bDontTransform �� ��������������� ������
     * @return boolean true ���� ������� ��������� ������ �� ����
     **/
    public function bGetCache($sHash, &$sResult, $bDontTransform = false)
    {
        if (!$this->bCheckCache($sHash)){
            return false;
        }
        $sFileName = $this->sTmpPath . $sHash;
        $sContent = file_get_contents($sFileName);
        if ($bDontTransform){
            $sResult = $sContent;
            return true;
        }
        try {
            $sResult = unserialize($sContent);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
    /**
     *
     **/
     public function bCheckCache($sHash){
        if (!isset($this->sTmpPath)) return false;

        $sFileName = $this->sTmpPath . $sHash;

        if (!file_exists($sFileName)) {
            return false;
        }
        if (filemtime($sFileName) + $this->iExpireTime < time()) {
            return false;
        }
        return true;
     }
    /**
     *
     **/
    public function vSaveRemote($sHash, $sRemoteUrl){
        if (empty($sRemoteUrl)) return;
        $this->vSaveCache($sHash, file_get_contents($sRemoteUrl), true);
    }
    /**
     * @var string $sHash ��� ����
     * @var mixed $sData ���������� � ������� ���������� ������
     * @var boolean $bDontTransform �� ��������� ������������
     **/
    public function vSaveCache($sHash, $sData, $bDontTransform = false)
    {
        if (empty($sHash)) return;
        if (!isset($this->sTmpPath)) return;
        $sFileName = $this->sTmpPath . $sHash;
        if ($bDontTransform){
            $res = file_put_contents($sFileName, $sData);
        }else{
            $res = file_put_contents($sFileName, serialize($sData));
        }
        if ($res == false){
            throw new InfoException('Not writable cache. Check config and rights on tmp dir.'.$sFileName);
        }
    }
}
