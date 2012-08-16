<?php
 sotmarketAutoload('InfoExeption.php');
/**
 * Базовый класс для пакетов веб-сервисов
 *
 * @copyright   Copyright (c) 2009, SOTMARKET.RU
 * @version     0.0.2 изменения от 22.02.2011
 * @author      Смирнов Андрей
 */

abstract class Package
{
    /**
     * Код "Выполнено успешно"
     */

    const SUCCESS = '1';

    /**
     * Выполняет валидацию пакета
     *
     * @return bool
     */

    public function Validate()
    {
        return true;
    }

    /**
     *  Возвращает список полей для заполнения в данном классе в формате
     *  array(array(
    'type' => 'text',
    'name' => 'Email',
    'desc' => 'Электронный адресс'
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