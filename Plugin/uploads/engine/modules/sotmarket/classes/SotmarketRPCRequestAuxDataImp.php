<?php
class SotmarketRPCRequestAuxDataImp extends SotmarketRPCRequestAuxData {
	
	/**
	 * Код партнёрки.
	 * 
	 * @var integer
	 */
	public $affiliateSiteId;
	
	/**
	 * Хэш-код сессии посетителя на данном сайте (генерируется партнёркой).
	 * 
	 * @var string
	 */
	public $userSessionHash;
	
	/**
	 * Контрольный хэш сессии (генерируется сервером).
	 * 
	 * @var string
	 */
	public $verificationHash;
	/**
	 *
	 *
	 **/
	public $swHash = 'c1fac153fae49a954a86a6cae9b07bc0';

}
?>