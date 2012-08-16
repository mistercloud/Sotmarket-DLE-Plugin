<?php
/**
 * Экземпляр данного класса или его подклассов передаётся конструктору класса
 * SotmarketRPCClient и используется для генерации дополнительных
 * данных, скрыто передающихся при удалённых вызовах.
 * 
 * Данный класс реализует паттерн "null object": методы пусты.
 */
class SotmarketRPCClientCallback {
	
	/**
	 * Метод вызывается при каждом маршаллируемом вызове. Возвращаемый им 
	 * объект передаётся методу SotmarketRPCServerCallback::processAuxData() 
	 * на стороне сервера и может быть получен изнутри вызываемого метода 
	 * с помощью метода SotmarketRPCServer::getRequestAuxData().
	 * Если данный метод возвращает NULL, то серверный processAuxData() 
	 * не вызывается.
	 * 
	 * Данная реализация возвращает NULL.
	 * 
	 * @param string $className
	 * @param string $methodName
	 * @return SotmarketRPCRequestAuxData Или NULL.
	 */
	function getRequestAuxData($className, $methodName) {
		return NULL;
	}
	
	/**
	 * Вызывается после успешной десериализации RPC-ответа (до возврата
	 * управления RPC-клиенту), если ответ содержит экземпляр 
	 * SotmakretRPCResponseAuxData. Если данный метод выбрасывает 
	 * исключение, оно выбрасывается RPC-клиенту как есть.
	 * 
	 * Данная реализация ничего не делает.
	 * 
	 * @param string $className
	 * @param string $methodName
	 * @param SotmarketRPCResponseAuxData $data
	 * @return void
	 */
	function processResponseAuxData($className, $methodName, SotmarketRPCResponseAuxData $data) {
	}
}
?>