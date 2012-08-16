<?php
    /**
 * Пакет "Претензия на Заказ"
 *
 * @copyright   Copyright (c) 2010, SOTMARKET.RU
 * @version     0.0.1 изменения от 18.11.2010
 * @author      Ковылин Владимир ( k-v-n@inbox.ru )
 */

class PackageOrderPretension extends Package
{
    /**
     * Пометка "Отлично"
     */

    const MARK_GOOD = 1;

    /**
     * Пометка "Плохо"
     */

    const MARK_BAD = 2;

    /**
     * Пометка "Информация"
     */

    const MARK_INFO = 3;

    /**
     * Код ошибки: "Идентификатор заказа не задан"
     */

    const ORDER_ID_EMPTY = 100;

    /**
     * Код ошибки "Телефон не заполнен"
     */

    const PHONE_EMPTY = 200;

    /**
     * Код ошибки "Телефон заполнен не верно"
     */

    const PHONE_WRONG = 201;

    /**
     * Код ошибки: "Текст сообщения не задан"
     */

    const TEXT_EMPTY = 300;

    /**
     * Код ошибки: "Метка оформлена не верно"
     */

    const MARK_WRONG = 400;

    /**
     * Код ошибки: "Email не заполнено"
     */

    const EMAIL_EMPTY = 500;

    /**
     * Код ошибки: "Email заполнен не верно"
     */

    const EMAIL_WRONG = 501;

    /**
     * Код ошибки: "Имя не заполнено"
     */

    const NAME_EMPTY = 600;

    /**
     * Идентификатор заказа
     *
     * @var int
     */

    public $OrderID;

    /**
     * Контактрый телефон
     *
     * @var string
     */

    public $Phone;

    /**
     * Тема сообщения
     *
     * @var string
     */

    public $Subject;

    /**
     * Текст претензии
     *
     * @var string
     */

    public $Text;

    /**
     * Пометка, оценка
     *
     * @var const
     */

    public $Mark;

    /**
     * Имя отправителя претензии
     *
     * @var string
     */

    public $Name;

    /**
     * Email отправителя претензии
     *
     * @var string
     */

    public $Email;

    /**
     * Выполняет вылидацию пакета
     *
     * @throws    InfoException
     */

    public function Validate()
    {
        if ($this->OrderID && !(int)$this->OrderID) {
            throw new InfoException("Идентификатор заказа не указан", self::ORDER_ID_EMPTY);
        }

        if (!$this->Text) {
            throw new InfoException("Текст сообщения не указан", self::TEXT_EMPTY);
        }

        if ($this->Mark != self::MARK_GOOD && $this->Mark != self::MARK_BAD && $this->Mark != self::MARK_INFO) {
            throw new InfoException("Метка сообщения задана не верно", self::MARK_WRONG);
        }

        if ($this->Phone && !String::CheckForTelephone($this->Phone)) {
            throw new InfoException("Телефон заполнено не верно, укажите код страны, код города и номер телефона без разделителей, например 74957809898", self::PHONE_WRONG);
        }

        if ($this->Email && !String::CheckForEmail($this->Email)) {
            throw new InfoException("Email задан не верно", self::EMAIL_WRONG);
        }

        $check_contacts = ($this->Phone || $this->Name || $this->Email) ? true : false;

        if ($check_contacts && !$this->Phone) {
            throw new InfoException("Телефон не заполнен", self::PHONE_EMPTY);
        }

        if ($check_contacts && !$this->Email) {
            throw new InfoException("Email не заполнен", self::EMAIL_EMPTY);
        }

        if ($check_contacts && !$this->Name) {
            throw new InfoException("Имя не заполнено", self::NAME_EMPTY);
        }

        return true;
    }

    public static function aGetFields()
    {
        $aResult[] = array(
            'type' => 'int',
            'name' => 'OrderID',
            'desc' => 'Идентификатор заказа'
        );

        $aResult[] = array(
            'type' => 'text',
            'name' => 'Subject',
            'desc' => 'Тема сообщения'
        );

        $aResult[] = array(
            'type' => 'text',
            'name' => 'Text',
            'desc' => 'Текст сообщения'
        );
        $aResult[] = array(
            'type' => array(1 => 'Отлично', 2 => 'Плохо', 3 => 'Информация'),
            'name' => 'Mark',
            'desc' => 'Оценка сообщения'
        );
        $aResult[] = array(
            'type' => 'int',
            'name' => 'Phone',
            'desc' => 'Телефон для обращения'
        );
        $aResult[] = array(
            'type' => 'text',
            'name' => 'Email',
            'desc' => 'Электронный адресс'
        );
        $aResult[] = array(
            'type' => 'text',
            'name' => 'Name',
            'desc' => 'Имя'
        );
        return $aResult;
    }
}

?>