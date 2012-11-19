<?php
/**
**/
class SotmarketSerializer {

    /**
     * ������� "���������� � Base64"
     */

    private $mBase64Encode;
    const HEADER_ENCODING_BITS = 'Encodedbytes';

    /**
     * @param string $password ������������ ��� �������� ����������� ��������������� ������.
     * @param boolean $ascii ��������������� ��������� ������������ � ASCII-������������ ����.
     * @param boolean $zlib ������������ zlib.
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
     * ��������������� �������� $x, � ��������� ���� ��� �������� ����������� ������
     * (��� ���������� ���� ������������ ���������� ������������ �������� $password),
     * ������� ��������� ������� compress().
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
     * ��������, �������� serialize().
     *
     * ������������� �������� $s ������� uncompress(), ����������������� ���������
     * � ��������� ����������� ������ (�������� �������� ���).
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
     * ������ ������ � �������������� zlib (���� ����� �������� ������������ $zlib
     * � ���������� zlib �����������) � ascii-������������ (���� ����� ��������
     * ������������ $ascii).
     *
     * �������� � ��������� public-����� � ���� ������������.
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
     * ��������, �������� compress().
     *
     * @param string $s
     * @param string $sEncodingMask ����� �� ������� ���������� ��� ���������� �����
     * @return string
     */
    function uncompress($s, $sEncodingMask = null)
    {
      // �������� ���������, ���� �� ��� ���������� � ������� ��������
      // ���� ���������� ����, ���������� ���� �������� ������������� � ����������
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
     * ����������, ���������� �� ������ � base64
     */
    function bBase64Encode(){
      return $this->mBase64Encode;
    }
    /**
     * ����������, ������������ �� ASCII �����������
     **/
    function bASCIIEncode(){
      return $this->_ascii;
    }
    /**
     * �������� �� ���������� ������
     */
    function bGZipped(){
      return $this->_zlib;
    }
    /**
     *  ���������� ������ � ����������� � ��� � ����� ������� ���������� ����������
     **/
    function sGetEncodingBits(){
     $str = '';
     $str .= $this->bGZipped()?'1':'0';
     $str .= $this->bBase64Encode()?'1':'0';
     $str .= $this->bASCIIEncode()?'1':'0';
     return $str;
    }
    /**
     * ������� ���������� ���� �������� ��������� �������� � ������� ����������� ������� � ����������
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

    // 64 �������, �.�. 2^6. ����� ���� ������� ����� �� ����� �� 6 ���
    // � ������ ��������������� � ������� ������� �������.
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
     * ������������� ���������
     * @return void
     **/
    public function vSetHeader(){
        header($this->sHeaderLine());
    }
    /**
     * @return string ���������� ��������� � ��������� ������� ��������� ������
     */
    public function sHeaderLine(){
      return SotmarketSerializer::HEADER_ENCODING_BITS.': '.$this->sGetEncodingBits();
    }
    private $_password;
    private $_ascii;
    private $_zlib;
}

/**
 * �� ������� ��� ��� ��� ��� php fpm ���� ������� ���, �  ���� � �����������.
 *
 * �����! �������� ����� ���������� � ������� �����!
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