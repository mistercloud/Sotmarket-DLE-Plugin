<?php
 sotmarketAutoload('InfoExeption.php');
/**
 * ������� ����� ��� ������� ���-��������
 *
 * @copyright   Copyright (c) 2009, SOTMARKET.RU
 * @version     0.0.2 ��������� �� 22.02.2011
 * @author      ������� ������
 */

abstract class Package
{
    /**
     * ��� "��������� �������"
     */

    const SUCCESS = '1';

    /**
     * ��������� ��������� ������
     *
     * @return bool
     */

    public function Validate()
    {
        return true;
    }

    /**
     *  ���������� ������ ����� ��� ���������� � ������ ������ � �������
     *  array(array(
    'type' => 'text',
    'name' => 'Email',
    'desc' => '����������� ������'
    ),)
     *
     **/
    public static function aGetFields()
    {
    }

    /**
     *
     *
     **/
    public function vSetValues($aValues)
    {
        $aFields = $this->aGetFields();

        foreach ($aFields as $aField) {
            $sKeyName = $aField['name'];
            if (!isset($aValues[$sKeyName])) {
                throw new InfoException('Not set field ' . $sKeyName);
            }
            $this->$sKeyName = $aValues[$sKeyName];
        }
    }

}

?>