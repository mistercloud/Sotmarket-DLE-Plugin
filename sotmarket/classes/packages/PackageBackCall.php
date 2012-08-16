<?php
    /**
 * ����� "����� ��������� ������"
 *
 * @copyright   Copyright (c) 2010, SOTMARKET.RU
 * @version     0.0.2 ��������� �� 12.11.2010
 * @author      ������� �������� ( k-v-n@inbox.ru )
 */

class PackageBackCall extends Package
{
    /**
     * ��� ������ "������� �� ��������"
     */

    const PHONE_EMPTY = 200;

    /**
     * ��� ������ "������� �������� �� �����"
     */

    const PHONE_WRONG = 201;

    /**
     * ��� ������ "������ ������ �� �����"
     */

    const PRODUCTS_WRONG_FORMAT = 211;

    /**
     * �������
     *
     * @var string
     */

    public $Phone;

    /**
     * ������ ��������������� �������
     *
     * @var int[]
     */

    public $Products;

    /**
     * ��������� ��������� ������
     *
     * @throws    InfoException
     */

    public function Validate()
    {
        /**
         * �������� ��������
         */

        if (!$this->Phone) {
            throw new InfoException("���� ������� �� ���������!", PackageBackCall::PHONE_EMPTY);
        }
        elseif (!String::CheckForTelephone($this->Phone)) {
            throw new  InfoException("���� ������� ��������� �� �����, ������� ��� ������, ��� ������ � ����� �������� ��� ������������, �������� 74957809898", PackageBackCall::PHONE_WRONG);
        }
        return true;
    }

    public static function aGetFields()
    {
        $aResult[] = array(
            'type' => 'int',
            'name' => 'Phone',
            'desc' => '������� ��� ��������� ������'
        );
        return $aResult;
    }
}

?>