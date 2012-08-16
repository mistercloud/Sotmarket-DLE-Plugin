<?php
    /**
 * Пакет "Заказ технической помощи"
 *
 * @copyright   Copyright (c) 2010, SOTMARKET.RU
 * @version     0.0.2 изменения от 12.11.2010
 * @author      Ковылин Владимир ( k-v-n@inbox.ru )
 */

class PackageSupportCall extends PackageBackCall
{
    /**
     * Код ошибки "Товары не заданы"
     */

    const PRODUCTS_EMPTY = 210;

    /**
     * Выполняет вылидацию пакета
     *
     * @throws    InfoException
     */

    public function Validate()
    {
        /**
         * Проверим заполнены ли товары
         */

        if (!$this->Products) {
            throw new InfoException("Товары не заданы", self::PRODUCTS_EMPTY);
        }
        /**
         * Проверка списка товаров
         */

        if ($this->Products && !is_array($this->Products)) {
            throw new InfoException("Товары заданы не верно", PackageBackCall::PRODUCTS_WRONG_FORMAT);
        }
        elseif (is_array($this->Products))
        {
            foreach ($this->Products as $product_id)
            {
                if (!is_int($product_id)) {
                    throw new InfoException("Товары заданы не верно", PackageBackCall::PRODUCTS_WRONG_FORMAT);
                }
            }
        }
        /**
         * Проверки в родительском классе
         */

        return parent::Validate();
    }

    public static function aGetFields()
    {
        $aResult[] = array(
            'type' => 'int',
            'name' => 'OrderId',
            'desc' => 'Идентификатор заказа'
        );
        return $aResult;
    }
}

?>