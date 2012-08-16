<?php
	final class SotmarketRPCClientCallbackSimple extends SotmarketRPCClientCallback
	{
		/**
		 * ������������� �����
		 *
		 * @var int
		 */
		
		private $affiliateSiteId;

		/**
		 * �����������
		 *
		 * @param int				$affiliateSiteId	������������� ������������ �����
		 */
		
		function __construct($affiliateSiteId) 
		{
			$this->affiliateSiteId = $affiliateSiteId;
		}
		
		/**
		 * Overriden.
		 * 
		 * @return SotmarketRPCRequestAuxDataImp
		 */
		function getRequestAuxData($className, $methodName) 
		{
			$result 					= new SotmarketRPCRequestAuxDataSimple();
			$result->affiliateSiteId 	= $this->affiliateSiteId;
			return $result;
		}
		
		/**
		 * Overriden (with adjusted signature).
		 */
		function processResponseAuxData($className, $methodName, SotmarketRPCResponseAuxDataImp $data) 
		{
		}
	}
?>