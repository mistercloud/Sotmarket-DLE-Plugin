<?php
/**
 * ��������� ��� ������ ����
 * @ver 0.2
 * @author ������ �������
 **/

class SotmarketClientCache
{
    // @var $iTmpExpireInSeconds
    // ����� �� ������� ���������� �������
    // �� ��������� �� ���� ����

    protected $iExpireTime = 86400; //24 * 60 * 60;

    public function __construct($config)
    {
        if (!empty($config['tmpExpire'])) {
            $this->iExpireTime = $config['tmpExpire'] * 60 * 60;
        }
    }

    /**
     * @var string $sHash ��� ����
     * @var mixed $sResult ���������� � ������� ������������ ������ �� ����
     * @return boolean true ���� ������� ��������� ������ �� ����
     **/
    public function bGetCache($sHash, &$sResult)
    {
    }

    /**
     * @var string $sHash ��� ����
     * @var mixed $sResult ���������� � ������� ����������
     **/
    public function vSaveCache($sHash, $sResult)
    {
    }
}
