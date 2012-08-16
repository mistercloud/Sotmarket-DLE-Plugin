<?php
	/**
	 * TODO Убрать README.txt из корня проекта, все описания должны быть в PHPDoc.
	 *      Один хрен в этом readme уже устаревшие сведения.
	 */
	final class SotmarketRPCServer 
	{
		protected $mConfig;

		function __construct(SotmarketRPCServerCallback $callback, SotmarketConfig &$config) 
		{
			$this->_callback	= $callback;
			$this->mConfig		= $config;
		}

		/**
		 * Выполняет полный цикл обработки: анализ $_REQUEST, выполнение вызванного метода, 
		 * отдачу заголовков и ответа. Перед вызовом данного метода необходимо установить
		 * classpath, чтобы контроллер мог найти вызываемый класс (и его зависимости).
		 * 
		 * Исключений не выбрасывает, исключения кодируются в ответ. Исключения RPC (ошибки 
		 * маршаллизации) кодируются классом SotmarketRPCException; изнутри метода могут
		 * быть выброшены исключения любых подклассов класса SotmarketException, и эти 
		 * классы должны быть объявлены на клиенте.
		 */
		function processRequest() 
		{
     //var_dump(debug_backtrace());
            $ascii_coding	= isset($this->mConfig->config['rpc']['ascii_coding'])		? (bool)$this->mConfig->config['rpc']['ascii_coding']		: TRUE;
			$compress		= isset($this->mConfig->config['rpc']['compress_request'])	? (bool)$this->mConfig->config['rpc']['compress_request']	: FALSE;
			$base64			= isset($this->mConfig->config['rpc']['base64_encode'])		? (bool)$this->mConfig->config['rpc']['base64_encode']		: FALSE;
			$serializer		= new SotmarketSerializer('php-rpc', $ascii_coding, $compress, $base64);			
			$ok				= TRUE;
			
			$response	= array(
					'result' => NULL, 
					'exception' => NULL,
					'auxdata' => NULL);
			
			try
			{
				$this->checkAccess();
			}
			catch (Exception $e) 
			{
				$ok						= FALSE;
				$response['exception']	= $e;
			}

			if( $ok )
			{
				try 
				{
                    $request = $serializer->unserialize($_REQUEST['RPCRequest']);
                    assert('gettype($request) == "array"');
					assert('gettype(@$request["className"]) == "string"');
					assert('gettype(@$request["methodName"]) == "string"');
					assert('gettype(@$request["args"]) == "array"');
					assert('@$request["auxdata"] === NULL || ($request["auxdata"] instanceof SotmarketRPCRequestAuxData)');
				} 
				catch (Exception $e) 
				{
					$ok						= FALSE;
					$response['exception']	= new SotmarketRPCException("RPC server: request deserialization error");
				}
			}

			if ($ok) 
			{
				try {
					$response['auxdata'] = $this->_callback->processAuxData($request['className'], $request['methodName'], $request['auxdata'], $_SERVER['REMOTE_ADDR']);
					assert('$response["auxdata"] === NULL || ($response["auxdata"] instanceof SotmarketRPCResponseAuxData)');
				} catch (Exception $e) {
					$ok = FALSE;
					$response['exception'] = $e;
				}
			}
			
			if ($ok) {
				try {
					list($object, $method) = $this->getObjectAndMethod($request['className'], $request['methodName']);
					$args = $request['args'];
				} catch (Exception $e) {
					$ok = FALSE;
					dumpfile($e->__toString());
					$response['exception'] = new SotmarketException("RPC server: object or method not found: "
							. $request['className'] . '::' . $request['methodName']);
				}
			}
			
			if ($ok) {
				$this->_requestAuxData = $request['auxdata'];
				$this->_responseAuxData = $response['auxdata'];
				$this->_callerIP = $_SERVER['REMOTE_ADDR'];
				try {
					$response['result'] = $method->invokeArgs($object, $args);
				} catch (Exception $e) {
					$ok = FALSE;
					$response['exception'] = $e;
				}
				$this->_requestAuxData = NULL;
				$this->_responseAuxData = NULL;
				$this->_callerIP = NULL;
			}
			
			// Вызываем всегда, при любых ошибках.
			// Исключения игнорируем.
			try {
				$this->_callback->cleanup();
			} catch (Exception $e) {
			}

			/*
			 * Засвечиваем клиенту только пользовательские исключения,
			 * причём пересоздаём их, чтобы сбросить стек, в котором
			 * (в списках параметров) попадаются несериализуемые объекты.
			 * ВНИМАНИЕ! Попытка вынести этот код в отдельный метод
			 * приводит к непоняткам. 
			 */
			$e = $response['exception'];
			if ($e) {
				if ($e instanceof SotmarketException) {
					dumpfile($e, 'RPC SotmarketException');
					$e2 = new SotmarketException($e->errors, $e->warnings);
				} else {
					dumpfile($e, 'RPC Exception');
					$e2 = new SotmarketException('Внутренняя ошибка сервера');
				}
				$response['exception'] = $e2;
			}
			
			$s = $serializer->serialize($response);
			header('Content-Type: text/plain');
      header('encodedbytes:'.$serializer->sGetEncodingBits());
			header('Content-Length: ' . strlen($s));
			echo $s;
		}
		
		/**
		 * Используется при вызове RPC обычным HTTP-запросом (например, 
		 * file_get_contents()) без использования RPCClient. 
		 * 
		 * Ожидает GET-параметры:
		 * - class - имя класса;
		 * - method - имя метода;
		 * - args - urlencode(serialize(array $args)), необязателен если аргументов нет.
		 * 
		 * ЗАМЕЧАНИЕ. Удобный для вызова из браузера вариант arg1=xxx&arg2=yyy&...
		 *            не поддерживается, т.к. значения всех аргументов в этом случае -
		 *            строки, что усложнит вызываемый код и не имеет никакой пользы
		 *            для вызывающего кода.
		 *            
		 * Возвращаемое значение:
		 * - serialize(array('result' => ..., 'exception' => NULL));
		 * или:
		 * - serialize(array('result' => NULL, 'exception' => ...))
		 * 
		 * ЗАМЕЧАНИЕ. Чтобы не ставить вызывающий код в зависимость от наличия класса 
		 *            SotmarketException, возвращаемое исключение будет всегда класса 
		 *            Exception, без информации о стеке вызовов.
		 * 
		 * ЗАМЕЧАНИЕ. Поддержка auxdata (скрытых дополнительных параметров, например 
		 *            кода сессии и т.п.) отсутствует. RPCServerCallback вызывается 
		 *            с параметром requestAuxData=NULL, но возвращаемые им данные 
		 *            не отдаются клиенту (проверяются на корректность и выбрасываются).
		 */
		function processHttpRequest() 
		{
			try 
			{
				$this->checkAccess();

				$className 	= @$_GET['class'];
				$methodName = @$_GET['method'];
				
				list($object, $method) = $this->getObjectAndMethod($className, $methodName);
				
				if (array_key_exists('args', $_GET)) {
					$args = @unserialize($_GET['args']);
					assert(is_array($args));
				} else {
					$args = array();
				}
				
				$requestAuxData = NULL;
				$responseAuxData = $this->_callback->processAuxData($className, $methodName, $requestAuxData, $_SERVER['REMOTE_ADDR']);
				assert('$responseAuxData === NULL || ($responseAuxData instanceof SotmarketRPCResponseAuxData)');
				
				$this->_requestAuxData = $requestAuxData;
				$this->_responseAuxData = $responseAuxData;
				$this->_callerIP = $_SERVER['REMOTE_ADDR'];
				try {
					$result = $method->invokeArgs($object, $args);
					$this->_requestAuxData = NULL;
					$this->_responseAuxData = NULL;
					$this->_callerIP = NULL;
				} catch (Exception $e) {
					$this->_requestAuxData = NULL;
					$this->_responseAuxData = NULL;
					$this->_callerIP = NULL;
					throw $e;
				}
				
				$response = serialize(array('result' => $result, 'error' => NULL));
			} catch (Exception $e) {
				$response = serialize(array(
						'result' => NULL, 
						'error' => $e->getMessage()));
			}
			header('Content-Type: text/plain; charset=windows-1251');
			echo $response;
		}
		
		function getCallback() {
			return $this->_callback;
		}
		
		/**
		 * @return SotmarketRPCRequestAuxData Или NULL.
		 */
		function getRequestAuxData() {
			return $this->_requestAuxData;
		}
		
		/**
		 * @return SotmarketRPCResponseAuxData Или NULL.
		 */
		function getResponseAuxData() {
			return $this->_responseAuxData;
		}
		
		/**
		 * @return string
		 */
		function getCallerIP() {
			return $this->_callerIP;
		}
		
		/**
		 * @var SotmarketRPCServerCallback
		 */
		private $_callback;
		
		private $_requestAuxData;
		private $_responseAuxData;
		private $_callerIP;
		
		private function getObjectAndMethod($className, $methodName) 
		{
			$object = $this->_callback->getObject($className);
			assert('$object instanceof SotmarketRPCServerObject');
			
			assert(in_array($methodName, $object->rpcMethodNames()));
			$class = new ReflectionClass(get_class($object));
			assert('$class->hasMethod($methodName)');
			$method = $class->getMethod($methodName);
			assert('$method->isPublic()');
			return array($object, $method);
		}

		/**
		 * Проверяет возможность подключения к серверу, в случае если в доступе отказано - выбрасывает исключение
		 */

		protected function checkAccess()
		{
			/**
			 * Проверка IP сервера, с которого получен запрос
			 * По умолчанию выполняется
			 */

			if( !isset($this->mConfig->config['rpc']['validate_server']) || $this->mConfig->config['rpc']['validate_server'] )
			{
				if( !isset($_SERVER['REMOTE_ADDR']) || $_SERVER['REMOTE_ADDR'] == "" )
				{
					throw new SotmarketException("Доступ запрещен");
				}

				$IsValid = false;

				for($i=0; $i<count($this->mConfig->config['rpc']['allowed_rpc_servers']); $i++)
				{
					$IPs = gethostbynamel($this->mConfig->config['rpc']['allowed_rpc_servers'][$i]);

					for( $j=0; $j<count($IPs); $j++ )
					{
						if( $_SERVER['REMOTE_ADDR'] == $IPs[$j] )
						{
							$IsValid = true;
							break 2;
						}
					}
				}

				if(!$IsValid)
				{
					throw new SotmarketException("Доступ запрещен");
				}
			}	
		}
	}
?>