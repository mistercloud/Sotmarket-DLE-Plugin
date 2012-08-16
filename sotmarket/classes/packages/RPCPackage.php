<?php
    /**
 * Базовый класс для RPC пакетов
 *
 * @copyright   Copyright (c) 2009, SOTMARKET.RU
 * @version     0.0.1 изменения от 01.02.2010
 * @author      Ковылин Владимир ( k-v-n@inbox.ru )
 */

abstract class RPCPackage
{
    /**
     * Текст ошибки
     *
     * @var string
     */

    protected $Error;

    /**
     * Код ошибки
     *
     * @var int
     */

    protected $ErrorCode;

    /**
     * Выполняет вылидацию пакета
     *
     * @return    bool
     */

    abstract public function Validate();

    /**
     * Выполняет проверку на Email
     *
     * @param   string      $string        строка
     * @return  bool
     */

    public function isEmail($string)
    {
        if (!strlen($string)) return true;

        return preg_match("/^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,4}$/i", $string);
    }

    /**
     * Выполняет проверку на Email
     *
     * @param   string      $string        строка
     * @return  bool
     */

    public function isInteger($string)
    {
        if (!strlen($string)) return true;

        return preg_match("/^\d+$/i", $string);
    }

    /**
     * Проверяет состоит ли строка из буквенных символов
     * Допускается так же символы:
     *  - пробел " "
     *  - дефис  "-"
     *
     * @param   string      $string        строка
     * @return  bool
     */

    public function isSymbols($string)
    {
        if (!strlen($string)) return true;

        return preg_match('/^([a-zA-Zа-яА-Я]|[ \-])*$/i', $string);
    }

    /**
     * Выполняет проверку на соответсвие строки телефонному номеру
     *
     * @param   string      $string        строка
     * @return  bool
     */


    public function isPhone($string)
    {
        if (!strlen($string)) return false;

        return preg_match('/^([7]{1})([0-9]{10,11})*$/', $string);
    }

    /**
     * Возвращает текст ошибки
     *
     * @return string
     */

    public function getError()
    {
        return $this->Error;
    }

    /**
     * Возвращает код ошибки
     *
     * @return int
     */

    public function getErrorCode()
    {
        return $this->ErrorCode;
    }
}

?>