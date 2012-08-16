<?php
    /**
 * RPC пакет "Покупатель"
 *
 * @copyright   Copyright (c) 2011 SOTMARKET.RU
 * @version     0.0.3 изменения от 12.03.2011
 * @author      Ковылин Владимир ( k-v-n@inbox.ru )
 */

class RPCPackage_Customer extends RPCPackage
{
    /**
     * Имя не заполнено
     */

    const FIRST_NAME_EMPTY = 210;

    /**
     * Имя заполнено не верно
     */

    const FIRST_NAME_WRONG = 211;

    /**
     * Фамилия не заполнена
     */

    const LAST_NAME_EMPTY = 220;

    /**
     * Фамилия не заполнена
     */

    const LAST_NAME_WRONG = 221;

    /**
     * Контакты заполнены не верно
     */

    const CONTACTS_WRONG = 300;

    /**
     * Идентификатор пользователя
     *
     * @var int
     */

    public $CustomerID;

    /**
     * Имя пользователя
     *
     * @var    string
     */

    public $FirstName;

    /**
     * Пароль
     *
     * @var    string
     */

    public $Password;
    /**
     * Фамилия пользователя
     *
     * @var    string
     */

    public $LastName;

    /**
     * Массив контактов пользователя
     *
     * @var    RPCPackage_CustomerContact
     */

    public $Contacts;

    /**
     * Массив адресов пользователя
     *
     * @var    RPCPackage_CustomerAddress
     */

    public $Addresses;

    /**
     * Типы контактов
     *
     * @var    string[]
     */

    private $mContactTypes = array(
        RPCPackage_CustomerContact::EMAIL,
        RPCPackage_CustomerContact::ICQ,
        RPCPackage_CustomerContact::MOBILE,
        RPCPackage_CustomerContact::PHONE,
    );
    /**
     *
     * @var string[]
     **/
    private $mRequiredContactTypes = array(
        RPCPackage_CustomerContact::EMAIL,
        RPCPackage_CustomerContact::PHONE
    );

    function __construct()
    {
        $this->Contacts = array();
        $this->Addresses = array();
        $this->Addresses[0] = new RPCPackage_CustomerAddress();
    }

    /**
     * Выполняет вылидацию пакета
     *
     * @return    bool
     */

    public function Validate()
    {
        /**
         * Личные данные
         */

        if (!$this->FirstName) {
            throw new InfoException("Поле 'Имя' не заполнено!", self::FIRST_NAME_EMPTY);
        }
        elseif ($this->FirstName && !$this->isSymbols($this->FirstName)) {
            throw new InfoException("Поле 'Имя' заполнено не верно", self::FIRST_NAME_WRONG);
        }

        if (!$this->LastName) {
            throw new InfoException("Поле 'Фамилия' не заполнено!", self::LAST_NAME_EMPTY);
        }
        elseif ($this->LastName && !$this->isSymbols($this->LastName)) {
            throw new InfoException("Поле 'Фамилия' заполнено не верно", self::LAST_NAME_WRONG);
        }
        if (count($this->Contacts) == 0) {
            throw new InfoException("Не заполнены контакты");
        }
        /**
         * Массив контактов пользователя
         */

        $errors = '';

        for ($i = 0; $i < count($this->Contacts); $i++)
        {
            try {
                $this->Contacts[$i]->Validate();
            }
            catch (Exception $e) {
                $errors .= $this->Contacts[$i]->Type . ':' . $e->getMessage() . '  <br />';
            }
        }

        if (!empty($errors)) {
            throw new InfoException($errors);
        }
        /**
         * Проверка на дубликаты
         */

        $this->checkForDublicates();
        $this->checkMandatoryContactTypes();
        return true;
    }

    /**
     * Возвращает значение контакта по умолчанию
     *
     * @param    connst    $contact_type    тип контакта
     * @return    string
     */

    public function getDefaultContactValue($contact_type)
    {
        for ($i = 0; $i < count($this->Contacts); $i++)
        {
            if ($this->Contacts[$i]->Type == $contact_type && $this->Contacts[$i]->IsDefault == 1) {
                return $this->Contacts[$i]->Value;
            }
        }

        return null;
    }

    /**
     * Выполняет проверку на дубликаты в контактных данных
     *
     * @throw    SotmarketRPCException
     */

    public function checkForDublicates()
    {
        for ($i = 0; $i < count($this->mContactTypes); $i++)
        {
            $Type =& $this->mContactTypes[$i];

            for ($j = 0; $j < count($this->Contacts); $j++)
            {
                if ($this->Contacts[$j]->Type != $Type) continue;

                for ($z = ($j + 1); $z < count($this->Contacts); $z++)
                {
                    if ($this->Contacts[$z]->Type != $Type) {
                        continue;
                    }
                    elseif ($this->Contacts[$z]->Type == $Type && $this->Contacts[$j]->Value == $this->Contacts[$z]->Value)
                    {
                        throw new SotmarketRPCException("Контактные данные дублируются!");
                    }
                }
            }
        }
    }

    /**
     * Проверяем что есть емаил или телефон
     **/
    private function checkMandatoryContactTypes()
    {
        $iPoints = 0;
        for ($i = 0; $i < count($this->Contacts); $i++)
        {
            if (in_array($this->Contacts[$i]->Type, $this->mRequiredContactTypes) && $this->Contacts[$i]->Validate()) {
                $iPoints++;
            }
        }
        if ($iPoints == 0) {
            throw new SotmarketRPCException("Не заполнены обязательные контакты!");
        }
    }

    /**
     * Геттер, позволяет получать данные из подклассов
     **/
    public function __get($varName)
    {
        if (preg_match("/([A-Za-z]+)\_([a-z]+)/", $varName, $aMatches)) {
            switch ($aMatches[2]) {
                case 'contact':
                    $sContactType = $aMatches[1];
                    // попытаемся найти контакт такого типа
                    //var_dump($this);
                    foreach ($this->Contacts as $oContact) {
                        if ($oContact->Type == $sContactType) {
                            // нашли переписываем значение в нем
                            return $oContact->Value;
                        }
                    }
                    return null;
                    break;
                case 'address':
                    // только 1 адрес
                    $iId = (int)$aMatches[1];
                    if ($this->Addresses[$iId])
                        return $this->Addresses[$iId];
                    return null;
                    break;
            }
        }
        return '';
    }
}

?>