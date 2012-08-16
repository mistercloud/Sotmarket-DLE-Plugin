<?php
/**
 * Интерфейс для класса кэша
 * @ver 0.2
 * @author Андрей Смирнов
 **/

class SotmarketClientCache
{
    // @var $iTmpExpireInSeconds
    // время на которое кэшируются контент
    // на умолчанию на один день

    protected $iExpireTime = 86400; //24 * 60 * 60;

    public function __construct($config)
    {
        if (!empty($config['tmpExpire'])) {
            $this->iExpireTime = $config['tmpExpire'] * 60 * 60;
        }
    }

    /**
     * @var string $sHash хэш кэша
     * @var mixed $sResult переменная в которой возвращаются данные из кэша
     * @return boolean true если удалось прочитать данные из кэша
     **/
    public function bGetCache($sHash, &$sResult)
    {
    }

    /**
     * @var string $sHash хэш кэша
     * @var mixed $sResult переменная в которой передаются
     **/
    public function vSaveCache($sHash, $sResult)
    {
    }
}
