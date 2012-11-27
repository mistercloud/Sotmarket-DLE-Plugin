<?php
	final class SotmarketRPCClientCallbackImp extends SotmarketRPCClientCallback 
	{
		/**
		 * �����������
		 *
		 * @param int				$affiliateSiteId	������������� ������������ �����
		 * @param SotmarketSession 	$session			������ ������������
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
		public function getRequestAuxData($className, $methodName) {
			$result 					= new SotmarketRPCRequestAuxDataImp();
			$result->affiliateSiteId 	= $this->_affiliateSiteId;
			$result->userSessionHash	= $this->_session;
			$result->verificationHash 	= @$_SESSION['sotmarket:serverSessionValidationHash'];
			
			return $result;
		}
		
		/**
		 * Overriden (with adjusted signature).
		 */
		function processResponseAuxData($className, $methodName, SotmarketRPCResponseAuxData $data) {
			@$_SESSION['sotmarket:serverSessionValidationHash'] = $data->verificationHash;
		}
		
		private $_affiliateSiteId;
		
		/**
		 * @var SotmarketSession
		 */
		private $_session;
	}
?>