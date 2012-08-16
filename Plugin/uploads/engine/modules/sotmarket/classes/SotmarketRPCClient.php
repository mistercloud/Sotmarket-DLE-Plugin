<?php
 /**
 * Клиент работающий с RPC сервером
 *
 * @copyright   Copyright (c) 2011, SOTMARKET.RU
 * @version     0.2.1  8.04.2011
 * @author      Ковылин Владимир ( k-v-n@inbox.ru )
 * @author      Андрей Смирнов () переработка
 **/

class SotmarketRPCClient
{
    protected $_config;
    private $mIPs = array();
    private $mUserAgents = array();

    //@var SotmarketRPCClientCallback
    protected $_callback;

    private $_objectsByClassName = array();
    private $_aTasks = array();
    // @var SotmarketClientCache
    private $_oCache = null;
    CONST UPDATE_SERVER_FILES = 'http://files.sotmarket.ru/forum/';
    /**
     * @param array $config
     * @param SotmarketRPCClientCallback $callback
     */
    function __construct(array $config, SotmarketRPCClientCallback $callback)
    {
        $this->_config = $config;
        $this->_callback = $callback;
        $this->_oCache = new SotmarketClientCacheFile($this->_config);
    }
    /**
     * @param $className
     **/
    function getObjectByClassName($className)
    {

        assert('gettype($className) == "string" && preg_match("/^[a-z_][a-z0-9_]*$/i", $className)');

        if (isset($this->_objectsByClassName[$className])) {
            return $this->_objectsByClassName[$className];
        }

        if (class_exists($className, TRUE)) {
            $rc = new ReflectionClass($className);
            assert('$rc->hasMethod("instance")');
            $rm = $rc->getMethod("instance");
            assert('$rm->isPublic()');
            assert('$rm->isStatic()');
            $result = $rm->invoke(NULL);
            assert('$result instanceof ' . $className);
        } else {
            $result = new SotmarketRPCClientProxy($this->_config, $this->_callback, $className);
        }
        $this->_objectsByClassName[$className] = $result;
        return $result;
    }


    /**
     * Функция добавляет задачу для обращения через RPC
     * @var string $sTaskName названия задачи
     * @var string $sClassName названия удаленного класса
     * @var string $sMethod название вызываемого метода
     * @var array $aArgs массив аргументов
     **/
    public function vAddTask($sTaskName, $sClassName, $sMethod, $aArgs = array())
    {
        /**
         * При добавлении новой задачи, проверим не находится ли она уже в кэше
         **/
        $bCached = false;
        if (preg_match('@(.+)_cached@', $sMethod, $aMatches)) {
            $sMethod = $aMatches[1];
            $bCached = true;
            $sCacheHash = $sMethod . md5($sClassName . '_' . serialize($aArgs));
            if ($this->_oCache->bGetCache($sCacheHash, $sResult)) {
                $this->_aResponse[$sTaskName] = $sResult;
                return;
            }
        }
        // Если задачи нет в кэше, добавляем её в список задач
        $this->_aTasks[$sTaskName] = array(
            'className' => $sClassName,
            'methodName' => $sMethod,
            'saveCache' => $bCached,
            'args' => $aArgs,
            'auxdata' =>
            $this->_callback->getRequestAuxData($sClassName, $sMethod));
    }

    /**
     * Произведем обращение к RPC серверу за нашими задачами.
     **/
    public function process()
    {
        if ($this->isSpider(@$_SERVER['REMOTE_ADDR'], @$_SERVER['HTTP_USER_AGENT'])) {
            throw new SotmarketRPCException('Робот');
        }
        // если нет задач, нечего запускать
        if (count($this->_aTasks) == 0) return;
        // на всякий случай создаю переменную не true/false а 1/0
        $iMultiple = (count($this->_aTasks) > 1) ? 1 : 0;

        if ($iMultiple === 1) {
            $aRequestData = $this->_aTasks;
        } else {
            $aRequestData = current($this->_aTasks);
        }

        $serializer = new SotmarketSerializer('php-rpc');
        $request = $serializer->serialize($aRequestData);
        $http = new SotmarketHttp();
        $url = $this->_config['serverUrl'];
        $get = array();
        $post = array('RPCRequest' => $request,
                      'multiple' => $iMultiple,
                      'site_id' => $this->_config['site_id']);
        // Для того чтобы передавать информацию в заголовках, надо установить заголовки в CURLOPT_HTTPHEADER
        // А для того, чтобы получать ответные заголовки надо установить CURLOPT_HEADER
        $headers = array($serializer->sHeaderLine());
        // Передадим UA и IP в заголовках
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $headers[] = 'mag-client-ua: ' . $_SERVER['HTTP_USER_AGENT'];
        }
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $headers[] = 'mag-client-ip: ' . $_SERVER['REMOTE_ADDR'];
        }
        $options = array(CURLOPT_HTTPHEADER => $headers, CURLOPT_HEADER => true);

        $response = $http->request($url, $get, $post, $options);

        if (!$response->ok()) {
            throw  new SotmarketRPCException("RPC client: HTTP request error " . $response->status() . " for URL: $url");
        }

        try {
            $aFullResponse = $serializer->unserialize($response->content(), $response->sGetHeader(SotmarketSerializer::HEADER_ENCODING_BITS));
            assert('gettype($aFullResponse) == "array"');
        } catch (SotmarketRPCException $e) {
            throw new SotmarketRPCException("error" . $response->content());
        } catch (Exception $e) {
            // Эта ошибка обычно вылетает, когда в поток попадают PHP errors/warnings/hints.
            dumpfile("RPC client: response deserialization error. Raw content: " . $response->content());
            throw new SotmarketException("RPC client: response deserialization error" . $response->content());
        }

        if (isset($aFullResponse['exception'])) {
            $e = $aFullResponse['exception'];
            // для обратной совместимости приложений использующих клиент
            if ($e instanceof InfoRPCException) {
                $e2 = new InfoException($e->getMessage(), $e->getCode());
                $e = $e2;
            }
            throw $e;
        }
        /**
         * Меняю к новой структуре запросов
         **/
        if ($iMultiple !== 1) {
            $sTaskName = key($this->_aTasks);
            $aNewResponse = array($sTaskName => $aFullResponse);
            $aFullResponse = $aNewResponse;
        }
        foreach ($aFullResponse as $sTaskName => $aTaskResponse) {
            if (!$aTaskResponse) continue;
            if ($aTaskResponse['auxdata']) {
                $this->_callback->processResponseAuxData($this->_aTasks[$sTaskName]['className'],
                                                         $this->_aTasks[$sTaskName]['methodName'], $aTaskResponse['auxdata']);
            }
            if (isset($aTaskResponse['exception'])) {
                $re = $aTaskResponse['exception'];
            } else {
                $re = $aTaskResponse['result'];
                // Сохраняем результат в кэше, если это надо делать
                if ($this->_aTasks[$sTaskName]['saveCache']) {
                    $sCacheHash = $this->_aTasks[$sTaskName]['methodName'] . md5($this->_aTasks[$sTaskName]['className'] . '_' . serialize($this->_aTasks[$sTaskName]['args']));
                    $this->_oCache->vSaveCache($sCacheHash, $re);
                }
            }
            $this->_aResponse[$sTaskName] = $re;
        }
        // очищаем задачи
        $this->_aTasks = null;
    }

    /**
     *
     *  Функция возвращает полученную информацию по задаче
     * @var string $sTaskName название задачи
     * @return mixed|false полученные данные от RPC сервера
     **/
    public function aGetData($sTaskName)
    {
        if (!isset($this->_aResponse[$sTaskName]))
            return false;
        if ($this->_aResponse[$sTaskName] instanceof Exception
            || $this->_aResponse[$sTaskName] instanceof InfoException) {
            throw $this->_aResponse[$sTaskName];
        }
        return $this->_aResponse[$sTaskName];
    }

    /**
     * Проверка ip и юзерагентов на принадлежность к ботам.
     **/
    public function isSpider($user_ip, $user_agent)
    {
        if (empty($_COOKIE["PHPSESSID"])) session_start();
        if (isset($_SESSION['sotmarket_spider'])){
            return $_SESSION['sotmarket_spider'];
        }
        /**
         * Выполняет проверку по IP
         */
        $this->_vLoadSpiderFiles();
        if ($user_ip) {
            for ($i = 0; $i < count($this->mIPs); $i++)
            {
                if ($this->mIPs[$i] == $user_ip) {
                    $_SESSION['sotmarket_spider'] = true;
                    return true;
                }
            }
        }

        /**
         * Выполняет проверку на User Agent
         */
        $user_agent = strtolower($user_agent);
        if ($user_agent) {
            {
                for ($i = 0; $i < count($this->mUserAgents); $i++)
                    if (substr_count($user_agent, $this->mUserAgents[$i])) {
                        $_SESSION['sotmarket_spider'] = true;
                        return true;
                    }
            }
        }
        $_SESSION['sotmarket_spider'] = false;
        return false;
    }
    /**
     * @throws SotmarketException в случае если файл не найден, проверьте пути..
     * Файл должен быть
     * @return
     */
    private function _vLoadSpiderFiles()
    {
        if (!empty($this->mUserAgents)) return;
        $this->_vLoadFileInArray('mUserAgents', 'spiders_ban.txt');
        $this->_vLoadFileInArray('sFileIps', 'ips_ban.txt');
    }
    /**
     * Загрузка файлов
     * И проверка нужно ли обновлять файлы.
     **/
    private function _vLoadFileInArray($sArrName, $sFile){
         $sFullName = $this->_config['data'] . $sFile;
         $this->_updateBanFiles($sFullName, $sFile);
         if (!is_file($sFullName)) {
            throw new SotmarketException("Не найден файл " . $sFullName);
        }
        $this->$sArrName = array_map('rtrim', file($sFullName));
    }
    /**
     * @param  string $sLocalFile
     * @param  string $sFile
     * @return void
     **/
    private function _updateBanFiles($sLocalFile, $sFile){
        $iExpireTime = 24*7*60*60; // неделя
        if (filemtime($sLocalFile) + $iExpireTime < time()) {   
            $sRemoteFile = SotmarketRPCClient::UPDATE_SERVER_FILES . '/'. $sFile;
            $sContent = @file_get_contents($sRemoteFile);
            if (empty($sContent)){
                @touch($sLocalFile);
            }else{
                @file_put_contents($sLocalFile, $sContent);
            }
        }
    }
}
