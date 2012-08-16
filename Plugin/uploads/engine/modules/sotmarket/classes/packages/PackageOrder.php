<?php
    /**
 * ����� "�����"
 *
 * @copyright   Copyright (c) 2010, SOTMARKET.RU
 * @version     0.0.1 ��������� �� 26.03.2010
 * @author      ������� �������� ( k-v-n@inbox.ru )
 */

class PackageOrder extends Package
{
    /**
     * ������������� ������
     *
     * @var int
     */

    public $OrderID;

    /**
     * ���� ������
     *
     * @var string
     */

    public $Date;

    /**
     * ������������� ������� ������
     *
     * @var string
     */

    public $StatusID;

    /**
     * �������� �������� �������
     *
     * @var string
     */

    public $StatusTitle;

    /**
     * ������ ���������
     *
     * @var RPCPackage_Product[]
     */

    public $Products;

    /**
     * ��������� ��������� ������
     *
     * @throws    SotmarketException
     */

    public function Validate()
    {
        return true;
    }
}

?>