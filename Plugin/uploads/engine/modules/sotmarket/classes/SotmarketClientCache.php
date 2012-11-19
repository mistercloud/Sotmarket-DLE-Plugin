<?php
/**
 * ��������� ��� ������ ����
 * @ver    0.4
 * @author ������ �������
 **/

class SotmarketClientCache {
    // @var $iTmpExpireInSeconds
    // ����� �� ������� ���������� �������
    // �� ��������� �� ���� ����

    protected $iExpireTime = 86400; //24 * 60 * 60;
    protected $sLocalCacheDir = null; // ���� ���� ��������� �����
    protected $iDirNameLength = 1;

    public static function getInstance($config) {
        $sType = isset($config['cacheType']) ? $config['cacheType'] : '';
        switch ($sType) {
            case 'file':
                return new SotmarketClientCacheFile($config);
            default:
                return new SotmarketClientCache($config);
        }
    }

    public function __construct($config) {
        if (!empty($config['tmpExpire'])) {
            $this->iExpireTime = $config['tmpExpire'] * 60 * 60;
        }
        if (empty($config['tmpPath'])) {
            throw new Exception('please, add tmpPath in config section');
        }
        $this->sLocalCacheDir = $config['tmpPath'];
        if (isset($config['dirLength'])) {
            $this->iDirNameLength = (int) $config['dirLength'];
        }
    }

    /**
     * @var string $sHash   ��� ����
     * @var mixed  $sResult ���������� � ������� ������������ ������ �� ����
     * @return boolean true ���� ������� ��������� ������ �� ����
     **/
    public function bGetCache($sHash,&$sResult) {
        return false;
    }

    /**
     * @var string $sHash   ��� ����
     * @var mixed  $sResult ���������� � ������� ����������
     **/
    public function vSaveCache($sHash,$sResult) {
        return;
    }

    /**
     * �� hash ��������� ��� ������ ������������� ����.
     * @param string $sHash  ��� �����
     * @param bool   $bMkDir ���� true �� ������ ������������ ���� � �����, �� � ��������� ���������� �� ���� � ����� �����
     * @return string ������������ ���� � ����� �� �������
     */
    public function sGetFileName($sHash,$bMkDir = false) {
        $sDirPrefix = '';
        if ($this->iDirNameLength > 0) {
            $sDirPrefix = substr($sHash,0,$this->iDirNameLength);
            if ($bMkDir && !is_dir($this->sLocalCacheDir . $sDirPrefix)) {
                mkdir($this->sLocalCacheDir . $sDirPrefix);
	            chmod($this->sLocalCacheDir . $sDirPrefix,0777);
            }
            $sDirPrefix .= '/';
        }
        return $this->sLocalCacheDir . $sDirPrefix . $sHash;
    }

    /**
     * ������������� ���� � ����� � ����
     * @param $sHash
     * @return string
     */
    public function sGetRelativePath($sHash) {
        $sDirPrefix = '';
        if ($this->iDirNameLength > 0) {
            $sDirPrefix = substr($sHash,0,$this->iDirNameLength) . '/';
        }
        return $sDirPrefix . $sHash;
    }
}
