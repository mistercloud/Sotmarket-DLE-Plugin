<?php
	final class SotmarketRPCClientCallbackImp extends SotmarketRPCClientCallback 
	{
		/**
		 *  онструктор
		 *
		 * @param int				$affiliateSiteId	идентификатор партнерского сайта
		 * @param SotmarketSession 	$session			сесси€ пользовател€
		 */
		
		function __construct($affiliateSiteId, $session = null) {
			$this->_affiliateSiteId = $affiliateSiteId;
			$this->_session 		= $session;
		}
		
		/**
		 * Overriden.
		 * 
		 * @return SotmarketRPCRequestAuxDataImp
		 */
		function getRequestAuxData($className, $methodName) {
			$result 					= new SotmarketRPCRequestAuxDataImp();
			$result->affiliateSiteId 	= $this->_affiliateSiteId;
			$result->userSessionHash	= $this->_session;
			$result->verificationHash 	= @$_SESSION['sotmarket:serverSessionValidationHash'];
			
			return $result;
		}
		
		/**
		 * Overriden (with adjusted signature).
		 */
		function processResponseAuxData($className, $methodName, SotmarketRPCResponseAuxDataImp $data) {
			@$_SESSION['sotmarket:serverSessionValidationHash'] = $data->verificationHash;
		}
		
		private $_affiliateSiteId;
		
		/**
		 * @var SotmarketSession
		 */
		private $_session;
	}
?>