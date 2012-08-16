<?php
/**
**/
class SotmarketSerializer {

    /**
     * Признак "Кодировать в Base64"
     */

    private $mBase64Encode;
    const HEADER_ENCODING_BITS = 'Encodedbytes';

    /**
     * @param string $password Используется для проверки целостности сериализованных данных.
     * @param boolean $ascii Преобразовывать результат сериализации к ASCII-совместимому виду.
     * @param boolean $zlib Использовать zlib.
     */
    function __construct($password = "", $ascii = FALSE, $zlib = TRUE, $base64 = TRUE) {
        $this->_password = $password;
        $this->_ascii = $ascii;
        $this->_zlib = $zlib
                && function_exists('gzcompress')
                && function_exists('gzuncompress');

        $this->mBase64Encode = $base64;
    }

    function zlib() {
        return $this->_zlib;
    }

    /**
     * Сериализовывает параметр $x, с дозаписью хеша для контроля целостности данных
     * (при вычислении хеша используется переданный конструктору параметр $password),
     * сжимает результат методом compress().
     *
     * @param mixed $x
     * @return string
     */
    function serialize($x) {
        $xs		= serialize($x);
        $hash	= md5($xs . $this->_password);
        $result = serialize(array(
            "x"		=> $xs,
            "hash"	=> $hash
        ));
        return $this->compress($result);
    }

    /**
     * Операция, обратная serialize().
     *
     * Распаковывает параметр $s методом uncompress(), десериализовывает результат
     * и проверяет целостность данных (повторно вычисляя хеш).
     *
     * @param string $s
     * @return mixed
     */
    function unserialize($s, $sEncodingMask = null) {
        $s = $this->uncompress($s, $sEncodingMask);
        $x = @unserialize($s);
        if (gettype($x) != "array"
                || count($x) != 2
                || gettype($x["x"]) != "string"
                || gettype($x["hash"]) != "string"
                || !preg_match("/^[0-9a-f]{32}$/", $x["hash"])
                || md5($x["x"] . $this->_password) != $x["hash"]) {
            throw new Exception("unserialize() failed");
        }
        return @unserialize($x["x"]);
    }

    /**
     * Сжатие строки с использованием zlib (если задан параметр конструктора $zlib
     * и расширение zlib установлено) и ascii-кодированием (если задан параметр
     * конструктора $ascii).
     *
     * Вынесено в отдельный public-метод в виду самоценности.
     *
     * @param string $s
     * @return string
     */
    function compress($s)
    {
        if ($this->bGZipped())
        {
            $s = @gzcompress($s, 9);
            if ($s === FALSE) {
                throw new Exception("gzcompress() failed");
            }
        }

        if ($this->bASCIIEncode()) {
            $s = $this->toAscii($s);
        }

        if($this->bBase64Encode()){
            $s = base64_encode($s);
        }

        return $s;
    }

    /**
     * Операция, обратная compress().
     *
     * @param string $s
     * @param string $sEncodingMask маска по которой определяем как зашифрован пакет
     * @return string
     */
    function uncompress($s, $sEncodingMask = null)
    {
      // Проверим заголовки, если ли там информация о способе упаковки
      // Если информация есть, выставляем биты согласно установленным в заголовках
      //die($sEncodingMask);
        if ($sEncodingMask == null){
            $aHeaders = getallheaders();
            if (isset($aHeaders[SotmarketSerializer::HEADER_ENCODING_BITS])){
                $this->vSetEncodingByMask($aHeaders[SotmarketSerializer::HEADER_ENCODING_BITS]);
            }
        }else{
            $this->vSetEncodingByMask($sEncodingMask);
        }
        if($this->bBase64Encode()){
            $s = base64_decode($s);
        }

        if ($this->bASCIIEncode()) {
            $s = $this->fromAscii($s);
        }

        if ($this->bGZipped())
        {
			$orig_str	= $s;
			$s 			= @gzuncompress($s);

			if ($s === FALSE) {
				throw new Exception("gzuncompress() failed: " . $orig_str);
            }
        }

        return $s;
    }
    /**
     * Возвращает, кодируется ли данные в base64
     */
    function bBase64Encode(){
      return $this->mBase64Encode;
    }
    /**
     * Возвращает, используется ли ASCII кодирование
     **/
    function bASCIIEncode(){
      return $this->_ascii;
    }
    /**
     * Сжиматся ли переданные данные
     */
    function bGZipped(){
      return $this->_zlib;
    }
    /**
     *  Возвращает строку с информацией о том в каком формате запакована информация
     **/
    function sGetEncodingBits(){
     $str = '';
     $str .= $this->bGZipped()?'1':'0';
     $str .= $this->bBase64Encode()?'1':'0';
     $str .= $this->bASCIIEncode()?'1':'0';
     return $str;
    }
    /**
     * Функция выставляет биты форматов запаковки согласно с данными переданными скрипты в заголовках
     **/
    function vSetEncodingByMask($sEncodingMask){
      if (isset($sEncodingMask[0])){
        $this->_zlib = ($sEncodingMask[0] == '1');
      }
      if (isset($sEncodingMask[1])){
        $this->mBase64Encode = ($sEncodingMask[1] == '1');
      }
      if (isset($sEncodingMask[2])){
        $this->_ascii = ($sEncodingMask[2] == '1');
      }
    }

    // 64 символа, т.е. 2^6. Будем бить входной поток на куски по 6 бит
    // и каждый преобразовывать в элемент данного массива.
    protected static $ASCII_LETTERS = array(
            'A', 'a', 'B', 'b', 'C', 'c', 'D', 'd', 'E', 'e',
            'F', 'f', 'G', 'g', 'H', 'h', 'I', 'i', 'J', 'j',
            'K', 'k', 'L', 'l', 'M', 'm', 'N', 'n', 'O', 'o',
            'P', 'p', 'Q', 'q', 'R', 'r', 'S', 's', 'T', 't',
            'U', 'u', 'V', 'v', 'W', 'w', 'X', 'x', 'Y', 'y',
            'Z', 'z', '0', '1', '2', '3', '4', '5', '6', '7',
            '8', '9', '_', '.');

    protected function toAscii($s) {
        if (strlen($s) == 0) {
            return "";
        }

        $bits = "";
        for($i = 0;  $i < strlen($s);  $i++) {
            $bits .= str_pad(decbin(ord($s[$i])), 8, '0', STR_PAD_LEFT);
        }

        $result = "";
        $lastbits = 0;
        while(($n = strlen($bits)) > 0) {
            if ($n > 6) {
                $c = bindec(substr($bits, 0, 6));
                $bits = substr($bits, 6);
            } else {
                $c = bindec($bits);
                $bits = "";
                $lastbits = $n;
            }
            $result .= self::$ASCII_LETTERS[$c];
        }
        $result .= $lastbits;
        return $result;
    }

    protected function fromAscii($s) {
        static $letters_preg = NULL;
        static $letters_flipped = NULL;
        if (!$letters_preg) {
            $letters_preg = preg_quote(implode("", self::$ASCII_LETTERS));
            $letters_flipped = array_flip(self::$ASCII_LETTERS);
        }

        $n = strlen($s);
        if ($n == 0) {
            return "";
        }
        if (!preg_match("/^[$letters_preg]+[1-6]$/", $s)) {
            throw new Exception("Illegal ASCII input: preg_match() failed");
        }

        $lastbits = $s[$n - 1] * 1;
        $bits = "";
        for($i = 0;  $i < $n - 1;  $i++) {
            $numbits = ($i == $n - 2) ? $lastbits : 6;
            $bits .= str_pad(decbin($letters_flipped[$s[$i]]), $numbits, '0', STR_PAD_LEFT);
        }

        $n = strlen($bits);
        if (($n % 8) != 0) {
            throw new Exception("Illegal ASCII input: bitstring restore failed");
        }

        $result = "";
        for ($i = 0;  $i < $n;  $i += 8) {
            $result .= chr(bindec(substr($bits, $i, 8)));
        }
        return $result;
    }
    /**
     * Устанавливает заголовки
     * @return void
     **/
    public function vSetHeader(){
        header($this->sHeaderLine());
    }
    /**
     * @return string возвращает заголовок с указанием способа кодировки пакета
     */
    public function sHeaderLine(){
      return SotmarketSerializer::HEADER_ENCODING_BITS.': '.$this->sGetEncodingBits();
    }
    private $_password;
    private $_ascii;
    private $_zlib;
}

/**
 * Не удаляем это так как для php fpm этой функции нет, и  надо её дублировать.
 *
 * Важно! название полей начинается с большой буквы!
 **/
if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
        $headers = array();
        foreach ($_SERVER as $name => $value)
        {
            if (substr($name, 0, 5) == 'HTTP_')
            {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
?>