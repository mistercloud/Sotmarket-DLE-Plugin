<?php
/**
 * @ver    0.4
 * @author ������ �������
 **/

class SotmarketClientCacheFile extends SotmarketClientCache {
    /**
     * @var string  $sHash          ��� ����
     * @var mixed   $sResult        ���������� � ������� ������������ ������ �� ����
     * @var boolean $bDontTransform �� ��������������� ������
     * @return boolean true ���� ������� ��������� ������ �� ����
     **/
    public function bGetCache($sHash,&$sResult,$bDontTransform = false) {
        if (!$this->bCheckCache($sHash)) {
            return false;
        }
        $sFileName = $this->sGetFileName($sHash);
        $sContent  = file_get_contents($sFileName);
        if ($bDontTransform) {
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
     * ��������� ���� �� � ���� ���� � �� ������ �� ���� �������� ����� � ����
     * @param $sHash ��� ������� �������������� ���� � ����
     * @return bool
     */
    public function bCheckCache($sHash) {
        if (!isset($this->sLocalCacheDir)) return false;

        $sFileName = $this->sGetFileName($sHash);

        if (!file_exists($sFileName)) {
            return false;
        }

        if (filemtime($sFileName) + $this->iExpireTime < time()) {
            return false;
        }
        return true;
    }

    /**
     * ��������� ���� �� URL � ��������� ���
     * @param $sHash      hash ����� ��� ������� �� ���������� � ����
     * @param $sRemoteUrl ����� �����
     * @return void
     */
    public function vSaveRemote($sHash,$sRemoteUrl) {
        if (empty($sRemoteUrl)) return;
        $this->vSaveCache($sHash,file_get_contents($sRemoteUrl),true);
    }

    /**
     * @var string  $sHash          ��� ����
     * @var mixed   $sData          ���������� � ������� ���������� ������
     * @var boolean $bDontTransform �� ��������� ������������
     * @throw Exception � ������ ���� ������ �������� ���
     **/
    public function vSaveCache($sHash,$sData,$bDontTransform = false) {
        if (empty($sHash)) return;
        if (!isset($this->sLocalCacheDir)) return;
        if (!$bDontTransform) {
            $sData = serialize($sData);
        }
        $sFileName = $this->sGetFileName($sHash,true);
        $res       = file_put_contents($sFileName,$sData);
        if ($res == false) {
            throw new InfoException('Not writable cache. Check config and rights on tmp dir. ' . $sFileName);
        }
    }
}