<?php
    /**
 * ����� "����� ����������� ������"
 *
 * @copyright   Copyright (c) 2010, SOTMARKET.RU
 * @version     0.0.2 ��������� �� 12.11.2010
 * @author      ������� �������� ( k-v-n@inbox.ru )
 */

class PackageSupportCall extends PackageBackCall
{
    /**
     * ��� ������ "������ �� ������"
     */

    const PRODUCTS_EMPTY = 210;

    /**
     * ��������� ��������� ������
     *
     * @throws    InfoException
     */

    public function Validate()
    {
        /**
         * �������� ��������� �� ������
         */

        if (!$this->Products) {
            throw new InfoException("������ �� ������", self::PRODUCTS_EMPTY);
        }
        /**
         * �������� ������ �������
         */

        if ($this->Products && !is_array($this->Products)) {
            throw new InfoException("������ ������ �� �����", PackageBackCall::PRODUCTS_WRONG_FORMAT);
        }
        elseif (is_array($this->Products))
        {
            foreach ($this->Products as $product_id)
            {
                if (!is_int($product_id)) {
                    throw new InfoException("������ ������ �� �����", PackageBackCall::PRODUCTS_WRONG_FORMAT);
                }
            }
        }
        /**
         * �������� � ������������ ������
         */

        return parent::Validate();
    }

    public static function aGetFields()
    {
        $aResult[] = array(
            'type' => 'int',
            'name' => 'OrderId',
            'desc' => '������������� ������'
        );
        return $aResult;
    }
}

?>