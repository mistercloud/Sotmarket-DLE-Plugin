<?php
 /**
 * RPC пакет "Контакт Покупателя"
 *
 * @copyright   Copyright (c) 2009, SOTMARKET.RU
 * @version     0.0.4 изменения от 05.04.2011
 * @author      Андрей Смирнов
 */

class RPCPackage_CustomerContact extends RPCPackage
{
    /**
     * Контакт заполнен не верно
     */
    const WRONG_FORMAT = 200;
    const PHONE_LENGTH = 11;

    /**
     * Тип контакта "Email"
     */

    const EMAIL = 'email';

    /**
     * Тип контакта "ICQ"
     */

    const ICQ = 'icq';

    /**
     * Тип контакта "Мобильный телефон"
     */

    const MOBILE = 'mobphone';

    /**
     * Тип контакта "Телефон"
     */

    const PHONE = 'phone';

    /**
     * Идентификатор контакта
     */

    public $ContactID;

    /**
     * Тип контакта
     */

    public $Type;

    /**
     * Значение
     */

    public $Value;

    /**
     * Признак "Имеет комментарий"
     */

    public $HasComments;

    /**
     * Комментарий
     */

    public $Comments;

    /**
     * Признак "Контакт по умолчанию"
     */

    public $IsDefault;

    /**
     * Конструктор объекта
     *
     * @return    bool
     */

    public function __construct()
    {
        $this->HasComments = 0;
        $this->IsDefault = 0;
    }

    /**
     * Выполняет вылидацию пакета
     *
     * @throws    SotmarketRPCException
     */

    public function Validate()
    {
        switch ($this->Type) {
            case self::EMAIL:
                if ($this->Value && !$this->isEmail($this->Value)) {
                    $this->ErrorCode = self::WRONG_FORMAT;
                    throw new InfoException("Поле EMAIL заполнено не верно, укажите email адрес", self::WRONG_FORMAT);
                }
                break;
            case self::ICQ:
                if ($this->Value && !$this->isInteger($this->Value)) {
                    $this->ErrorCode = self::WRONG_FORMAT;
                    throw new SotmarketRPCException("Поле ICQ заполнено не верно, введите цифры без разделителей!", self::WRONG_FORMAT);
                }
                break;
            case self::MOBILE:
            case self::PHONE:
                if ($this->Value && !$this->isInteger($this->Value)) {
                    $this->ErrorCode = self::WRONG_FORMAT;
                    throw new SotmarketRPCException("Поле телефон заполнено не верно, введите цифры без разделителей!", self::WRONG_FORMAT);
                }
                if (strlen($this->Value) != self::PHONE_LENGTH) {
                    $this->ErrorCode = self::WRONG_FORMAT;
                    throw new SotmarketRPCException("Поле телефон заполнено не верно, введите телефон в формате 9123456789!", self::WRONG_FORMAT);
                }
                // уберем телефон из шаблона
                if ($this->Value == '79123456789') {
                    $this->ErrorCode = self::WRONG_FORMAT;
                    throw new SotmarketRPCException("Поле телефон заполнено не верно, введите свой номер телефона!", self::WRONG_FORMAT);
                }
                break;
        }
        return true;
    }
}

?>