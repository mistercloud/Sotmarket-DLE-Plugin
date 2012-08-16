<?php
    /**
 * Пакет "Заказ"
 *
 * @copyright   Copyright (c) 2010, SOTMARKET.RU
 * @version     0.0.1 изменения от 26.03.2010
 * @author      Ковылин Владимир ( k-v-n@inbox.ru )
 */

class PackageOrder extends Package
{
    /**
     * Идентификатор заказа
     *
     * @var int
     */

    public $OrderID;

    /**
     * Дата заказа
     *
     * @var string
     */

    public $Date;

    /**
     * Идентификатор статуса заказа
     *
     * @var string
     */

    public $StatusID;

    /**
     * Название текущего статуса
     *
     * @var string
     */

    public $StatusTitle;

    /**
     * Список продуктов
     *
     * @var RPCPackage_Product[]
     */

    public $Products;

    /**
     * Выполняет вылидацию пакета
     *
     * @throws    SotmarketException
     */

    public function Validate()
    {
        return true;
    }
}

?>