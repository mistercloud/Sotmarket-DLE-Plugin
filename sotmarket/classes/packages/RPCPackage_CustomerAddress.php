<?php
    /**
 * RPC пакет "Адрес Покупателя"
 *
 * @copyright   Copyright (c) 2009, SOTMARKET.RU
 * @version     0.0.2 изменения от 12.01.2011
 * @author      Ковылин Владимир ( k-v-n@inbox.ru )
 */

class RPCPackage_CustomerAddress extends RPCPackage
{
    /**
     * Город не заполнен
     */

    const CITY_EMPTY = 200;

    /**
     * Адрес не заполнен
     */

    const ADDRESS_EMPTY = 210;

    /**
     * Метро не заполненл
     */

    const UNDERGROUND_EMPTY = 220;

    /**
     * Индекс не заполнено
     */

    const POST_CODE_EMPTY = 230;

    /**
     * Индекс заполнен не верно
     */

    const POST_CODE_WRONG = 231;

    /**
     *
     *
     **/
    const POSTCODE_MOSCOW = 109428;


    /**
     * Код регионального офиса "Россия"
     */

    const RUSSIA = "";

    /**
     * Идентификатор адреса
     *
     * @var int
     */

    public $AddressID;

    /**
     * Почтовый индекс
     *
     * @var int
     */

    public $PostCode;

    /**
     * Город
     *
     * @var string
     */

    public $City;

    /**
     * Идентификатор Регионального офиса
     *
     * @var int
     */

    public $CityID;

    /**
     * Улица, дом и т.п.
     *
     * @var string
     */

    public $Street;

    /**
     * Идентификатор станции метро
     *
     * @var int
     */

    public $UndergroundID;

    /**
     * Тип в адресной книге
     *
     * @var string
     */

    public $Type;

    /**
     * Массив идентификаторов городов, для которых требуется ввод станции метро
     *
     * @var int[]
     */

    protected $UndergroundRequired;

    /**
     * Массив идентификаторов городов, для которых требуется ввод почтового индекса
     *
     * @var int[]
     */

    protected $PostCodeRequired;

    /**
     * Конструктор
     */

    public function __construct()
    {
        $this->UndergroundRequired = array('77-0-0-0' => 1, '78-0-0-0' => 1, '66-0-1-0' => 1, '52-0-1-0' => 1, '2-1-1-0' => 1);
    }

    /**
     *
     * Идентификатор станции метро
     *
     * @var int
     */

    public function Validate()
    {
        if (empty($this->CityID) && !$this->City) {
            $this->Error = "Не заполнено поле \"Город\"";
            $this->ErrorCode = self::CITY_EMPTY;
            throw new InfoException($this->Error, $this->ErrorCode);
        }

        if (!$this->Street) {
            $this->Error = "Не заполнено поле \"Адрес\"";
            $this->ErrorCode = self::ADDRESS_EMPTY;
            throw new InfoException($this->Error, $this->ErrorCode);
        }

        if (isset($this->UndergroundRequired[$this->CityID]) && !$this->UndergroundID) {
            $this->Error = "Не заполнено поле \"Станция метро\"";
            $this->ErrorCode = self::UNDERGROUND_EMPTY;
            throw new InfoException($this->Error, $this->ErrorCode);
        }

        if ($this->PostCode && !$this->isInteger($this->PostCode)) {
            $this->Error = "Поле \"Индекс\" заполнено не верно";
            $this->ErrorCode = self::POST_CODE_WRONG;
            throw new InfoException($this->Error, $this->ErrorCode);
        }

        if (empty($this->CityID) && !$this->PostCode) {
            $this->Error = "Поле \"Индекс\" не заполнено";
            $this->ErrorCode = self::POST_CODE_EMPTY;
            throw new InfoException($this->Error, $this->ErrorCode);
        }
    }
}

?>