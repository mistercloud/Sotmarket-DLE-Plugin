<?php
	final class SotmarketRPCClientCallbackSimple extends SotmarketRPCClientCallback
	{
		/**
		 * Идентификатор сайта
		 *
		 * @var int
		 */
		
		private $affiliateSiteId;

		/**
		 * Конструктор
		 *
		 * @param int				$affiliateSiteId	идентификатор партнерского сайта
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