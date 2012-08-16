<?php
   /**
 * ����� "������� � ������"
 * ����� �������� � ������� �� ���������� ������
 *
 * @copyright   Copyright (c) 2010, SOTMARKET.RU
 * @version     0.0.1 ��������� �� 17.10.2010
 * @author      ������� �������� ( k-v-n@inbox.ru )
 */

class PackageOrderPosition extends Package
{
    /**
     * ������������� ��������
     *
     * @var int
     */

    public $product_id;

    /**
     * ������������� ���� ��������
     *
     * @var int
     */

    public $product_vid_id;

    /**
     * ����������
     *
     * @var int
     */

    public $quantity;

    /**
     * ���� ������� � ������
     *
     * @var int
     */

    public $price;

    /**
     * ������� ������
     *
     * @var string
     */

    public $model;

    /**
     * ��������� �������������� �� stdClass � PackageOrderPosition
     * ��� ������� ������ ���� stdClass �������� ��� �������� �� ��������� SOAP
     * ������ ���� stdClass ������ ��������� ��� �� ����� ������� ��� � PackageOrderPosition
     *
     * @param     stdClass                 $object        ������ ���� stdClass
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