<?php
    /**
 * ������� ����� ��� RPC �������
 *
 * @copyright   Copyright (c) 2009, SOTMARKET.RU
 * @version     0.0.1 ��������� �� 01.02.2010
 * @author      ������� �������� ( k-v-n@inbox.ru )
 */

abstract class RPCPackage
{
    /**
     * ����� ������
     *
     * @var string
     */

    protected $Error;

    /**
     * ��� ������
     *
     * @var int
     */

    protected $ErrorCode;

    /**
     * ��������� ��������� ������
     *
     * @return    bool
     */

    abstract public function Validate();

    /**
     * ��������� �������� �� Email
     *
     * @param   string      $string        ������
     * @return  bool
     */

    public function isEmail($string)
    {
        if (!strlen($string)) return true;

        return preg_match("/^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,4}$/i", $string);
    }

    /**
     * ��������� �������� �� Email
     *
     * @param   string      $string        ������
     * @return  bool
     */

    public function isInteger($string)
    {
        if (!strlen($string)) return true;

        return preg_match("/^\d+$/i", $string);
    }

    /**
     * ��������� ������� �� ������ �� ��������� ��������
     * ����������� ��� �� �������:
     *  - ������ " "
     *  - �����  "-"
     *
     * @param   string      $string        ������
     * @return  bool
     */

    public function isSymbols($string)
    {
        if (!strlen($string)) return true;

        return preg_match('/^([a-zA-Z�-��-�]|[ \-])*$/i', $string);
    }

    /**
     * ��������� �������� �� ����������� ������ ����������� ������
     *
     * @param   string      $string        ������
     * @return  bool
     */


    public function isPhone($string)
    {
        if (!strlen($string)) return false;

        return preg_match('/^([7]{1})([0-9]{10,11})*$/', $string);
    }

    /**
     * ���������� ����� ������
     *
     * @return string
     */

    public function getError()
    {
        return $this->Error;
    }

    /**
     * ���������� ��� ������
     *
     * @return int
     */

    public function getErrorCode()
    {
        return $this->ErrorCode;
    }
}

?>