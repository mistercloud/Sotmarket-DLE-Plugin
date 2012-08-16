<?php
final class SotmarketHttpResponse {

    /**
    * ������ �������� ������� SotmarketHttp.request().
    *
    * @param array $info ��������� ������ curl_getinfo().
    * @param string $content
    */
    function __construct($info, $content, $headers = '') {
        $this->_info = $info;
        $this->_content = $content;
        $this->_headers = $headers;
        $this->_aHeaders = null;
    }

    /**
    * @return integer HTTP-��� ������ (200=OK, 404=Not Found � �.�.).
    */
    function status() {
        return $this->_info["http_code"];
    }

    /**
    * @return boolean TRUE ���� HTTP-��� ������ 200, FALSE � ��������� ������.
    */
    function ok() {
        return $this->status() == 200;
    }

    /**
    * @return integer Timestamp �� ��������� Last-Modified, ��� -1 ���� ����������.
    **/
    function modified() {
        return $this->_info["filetime"];
    }
    /**
     *
     *  ��� ��������
     **/
    function contentType() {
        return $this->_info["content_type"];
    }

    /**
    * @return string ���������� ������.
    **/
    function content() {
        return $this->_content;
    }
    /**
     *@var string sHeaderName �������� ���������
     *@return false|string ����� ��������� ��� false � ������ ���� �� ������ ���������
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
     *  ������ ��������� � ������
     *
     **/
    function vParseHeaders(){
        // ������ ������ ���� ���
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