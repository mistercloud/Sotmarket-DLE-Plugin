<?php
    /**
 * Информационное сообщение.
 * Класс используется для передачи информационного сообщения между клиентом и сервером.
 * Текст сообщения транслируется по протоколам RPC и SOAP без преобразований.
 * Сериализуется с помощью сериализатора, реализованного в SotmarketRPCException
 *
 * @copyright   Copyright (c) 2009, SOTMARKET.RU
 * @version     0.1 изменения от 21.03.2011
 * @author      Андрей Смирнов
 */

class InfoRPCException extends SotmarketRPCException
{

}

?>