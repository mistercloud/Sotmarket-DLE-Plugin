<?php
final class SotmarketRPCClientProxy extends SotmarketRPCClient{
    protected $_config;
    /**
     *  Ответы на разные запросы
     **/
    protected $_aResponse;
    /**
     * Список для выполениния RPC запросов
     **/
    protected $_aTasks;
    /**
     * @var SotmarketRPCClientCallback
     */
    protected $_callback;

    private $_proxiedClassName;

	public function __construct(array $config, SotmarketRPCClientCallback $callback, $proxiedClassName) {
		parent::__construct($config, $callback);
		$this->_proxiedClassName = $proxiedClassName;
	}

	public function getProxiedClassName() {
		return $this->_proxiedClassName;
	}
    /**
     * @param  string 
     * @param  $args
     * @return
     */
	public function __call($methodName, $args) {
        $sTaskName = $this->_proxiedClassName . $methodName .md5(serialize($args));
        $this->vAddTask($sTaskName, $this->_proxiedClassName, $methodName, $args);
        $this->process();
        return $this->aGetData($sTaskName);
    }
}
?>