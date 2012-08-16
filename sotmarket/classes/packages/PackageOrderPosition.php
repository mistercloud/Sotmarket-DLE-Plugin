<?php
   /**
 * Пакет "Позиция в заказе"
 * Пакет приходит в запросе на оформление заказа
 *
 * @copyright   Copyright (c) 2010, SOTMARKET.RU
 * @version     0.0.1 изменения от 17.10.2010
 * @author      Ковылин Владимир ( k-v-n@inbox.ru )
 */

class PackageOrderPosition extends Package
{
    /**
     * Идентификатор продукта
     *
     * @var int
     */

    public $product_id;

    /**
     * Идентификатор вида продукта
     *
     * @var int
     */

    public $product_vid_id;

    /**
     * Количество
     *
     * @var int
     */

    public $quantity;

    /**
     * Цена позиции в заказе
     *
     * @var int
     */

    public $price;

    /**
     * Артикул товара
     *
     * @var string
     */

    public $model;

    /**
     * Выполняет преобразование из stdClass в PackageOrderPosition
     * Как правило объект типа stdClass приходит при передаче по протоколу SOAP
     * Объект типа stdClass должен содержать тот же набор свойств что и PackageOrderPosition
     *
     * @param     stdClass                 $object        объект типа stdClass
     * @return     PackageOrderPosition
     */

    public static function fromStdClass(stdClass $object)
    {
        $Position = new PackageOrderPosition;
        $Position->product_id = property_exists($object, 'product_id') ? (int)$object->product_id : null;
        $Position->product_vid_id = property_exists($object, 'product_vid_id') ? (int)$object->product_vid_id : null;
        $Position->quantity = property_exists($object, 'quantity') ? (int)$object->quantity : null;

        return $Position;
    }
}

?>