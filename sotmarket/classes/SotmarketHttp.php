<?php
class SotmarketHttp {

	/**
	 * @param string $url
	 * @param array $getParams, assoc.array or NULL
	 * @param array $postParams, assoc.array or NULL for GET request
	 * @return SotmarketHttpResponse
	 */
	function request($url, $getParams = NULL, $postParams = NULL, $options = array()) {
		$s = $this->encodeParams($getParams);
		if ($s) {
			$url .= (strpos($url, "?") === FALSE ? "?" : "&") . $s;	
		}

		$c = curl_init($url);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_FILETIME, 1);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($c, CURLOPT_TIMEOUT, 300);
		
		foreach ($options as $name => $value)
			curl_setopt($c, $name, $value);
			
		$s = $this->encodeParams($postParams);
		if ($s) {
			curl_setopt($c, CURLOPT_POST, 1);
			curl_setopt($c, CURLOPT_POSTFIELDS, $s);	
        }

    	$content = curl_exec($c);
        $headers = '';
        /**
         *  Если выставлен параметр CURLOPT_HEADER, то получаем данные в виде заголовки, два перевода, строки а потом текст ответа
         *  Надо разбить на два куска.
         */
        if (isset($options[CURLOPT_HEADER])){
            list($headers, $content) = preg_split("/\r\n\r\n/si", $content, 2);
            if ($headers == 'HTTP/1.1 100 Continue'){
                list($headers, $content) = preg_split("/\r\n\r\n/si", $content, 2);
            }
            if (isset($_GET['abc']))
                echo ($content);
        }
		$info = curl_getinfo($c);
		curl_close($c);
        return new SotmarketHttpResponse($info, $content, $headers);
	}

	/**
	 * @param array $params
	 * @return string
	 */
	private function encodeParams($params) {
		if (gettype($params) != "array") {
			return "";
		}
		$s = "";
		foreach($params as $name => $value) {
			$s .= ($s ? "&" : "") . $name . "=" . urlencode($value);
		}
		return $s;
	}
}
?>