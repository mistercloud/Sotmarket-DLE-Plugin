<?php
final class SotmarketHttpResponse {

    /**
    * Объект создаётся методом SotmarketHttp.request().
    *
    * @param array $info Результат вызова curl_getinfo().
    * @param string $content
    */
    function __construct($info, $content, $headers = '') {
        $this->_info = $info;
        $this->_content = $content;
        $this->_headers = $headers;
        $this->_aHeaders = null;
    }

    /**
    * @return integer HTTP-код ответа (200=OK, 404=Not Found и т.п.).
    */
    function status() {
        return $this->_info["http_code"];
    }

    /**
    * @return boolean TRUE если HTTP-код ответа 200, FALSE в противном случае.
    */
    function ok() {
        return $this->status() == 200;
    }

    /**
    * @return integer Timestamp из заголовка Last-Modified, или -1 если неизвестно.
    **/
    function modified() {
        return $this->_info["filetime"];
    }
    /**
     *
     *  тип контента
     **/
    function contentType() {
        return $this->_info["content_type"];
    }

    /**
    * @return string Содержимое ответа.
    **/
    function content() {
        return $this->_content;
    }
    /**
     *@var string sHeaderName название заголовка
     *@return false|string текст заголовка или false в случае если не найден заголовок
     **/
    function sGetHeader($sHeaderName){
        if ($this->_aHeaders == null){
            $this->vParseHeaders();
        }
        if (isset($this->_aHeaders[$sHeaderName])){
            return $this->_aHeaders[$sHeaderName];
        }
        return false;
    }
    /**
     *  Парсим заголовки в массив
     *
     **/
    function vParseHeaders(){
        // парсим только один раз
        if ($this->_aHeaders != null) return;

        $aSplit = preg_split("/\r\n/si", $this->_headers);

        foreach($aSplit as $sLine){
            $aPars = preg_split("/\: /", $sLine, 2);
            if (!isset($aPars[1])) continue;
            $this->_aHeaders[$aPars[0]] = $aPars[1];
        }
    }
  
    private $_info;
    private $_content;
    private $_headers;
    private $_aHeaders;
}

if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
        foreach ($_SERVER as $name => $value)
        {
        if (substr($name, 0, 5) == 'HTTP_')
        {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
        }
        return $headers;
    }                                                                                       }