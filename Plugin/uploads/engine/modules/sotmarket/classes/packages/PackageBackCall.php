<?php
    /**
 * Пакет "Заказ обратного звонка"
 *
 * @copyright   Copyright (c) 2010, SOTMARKET.RU
 * @version     0.0.2 изменения от 12.11.2010
 * @author      Ковылин Владимир ( k-v-n@inbox.ru )
 */

class PackageBackCall extends Package
{
    /**
     * Код ошибки "Телефон не заполнен"
     */

    const PHONE_EMPTY = 200;

    /**
     * Код ошибки "Телефон заполнен не верно"
     */

    const PHONE_WRONG = 201;

    /**
     * Код ошибки "Товары заданы не верно"
     */

    const PRODUCTS_WRONG_FORMAT = 211;

    /**
     * Телефон
     *
     * @var string
     */

    public $Phone;

    /**
     * Массив идентификаторов товаров
     *
     * @var int[]
     */

    public $Products;

    /**
     * Выполняет вылидацию пакета
     *
     * @throws    InfoException
     */

    public function Validate()
    {
        /**
         * Проверка телефона
         */

        if (!$this->Phone) {
            throw new InfoException("Поле Телефон не заполнено!", PackageBackCall::PHONE_EMPTY);
        }
        elseif (!String::CheckForTelephone($this->Phone)) {
            throw new  InfoException("Поле Телефон заполнено не верно, укажите код страны, код города и номер телефона без разделителей, например 74957809898", PackageBackCall::PHONE_WRONG);
        }
        return true;
    }

    public static function aGetFields()
    {
        $aResult[] = array(
            'type' => 'int',
            'name' => 'Phone',
            'desc' => 'Телефон для обратного звонка'
        );
        return $aResult;
    }
}

?>