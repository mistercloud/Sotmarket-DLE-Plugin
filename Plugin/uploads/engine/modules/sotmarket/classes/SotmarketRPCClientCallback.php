<?php
/**
 * ��������� ������� ������ ��� ��� ���������� ��������� ������������ ������
 * SotmarketRPCClient � ������������ ��� ��������� ��������������
 * ������, ������ ������������ ��� �������� �������.
 * 
 * ������ ����� ��������� ������� "null object": ������ �����.
 */
class SotmarketRPCClientCallback {
	
	/**
	 * ����� ���������� ��� ������ �������������� ������. ������������ �� 
	 * ������ ��������� ������ SotmarketRPCServerCallback::processAuxData() 
	 * �� ������� ������� � ����� ���� ������� ������� ����������� ������ 
	 * � ������� ������ SotmarketRPCServer::getRequestAuxData().
	 * ���� ������ ����� ���������� NULL, �� ��������� processAuxData() 
	 * �� ����������.
	 * 
	 * ������ ���������� ���������� NULL.
	 * 
	 * @param string $className
	 * @param string $methodName
	 * @return SotmarketRPCRequestAuxData ��� NULL.
	 */
	function getRequestAuxData($className, $methodName) {
		return NULL;
	}
	
	/**
	 * ���������� ����� �������� �������������� RPC-������ (�� ��������
	 * ���������� RPC-�������), ���� ����� �������� ��������� 
	 * SotmakretRPCResponseAuxData. ���� ������ ����� ����������� 
	 * ����������, ��� ������������� RPC-������� ��� ����.
	 * 
	 * ������ ���������� ������ �� ������.
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