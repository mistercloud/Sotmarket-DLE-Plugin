<?php
/**
 * ���������. ���������� ������ ���� ����������, �� ��� �� ����� 
 * � ����� ������ ����������� ���������� ����������� ����������� 
 * � ���������� ����. ������� ������ � ���������� ������ �����������
 * ����� ����� ���������� (SPI).
 */
final class SotmarketConfig {

	/**
	 * @var array
	 */
	public $config;

	/**
	 * ��������� � ������ ��������� �� ������ $path/config.php � 
	 * $path/config.host.php � $this->config. ��� �����, ���� ����������, 
	 * ������ ��������� ������������ ������������� ������ $config.
	 * ���� config.host.php �������� ��������������.
	 *
	 * @param string $path � ��������� ������ ��� ���.
	 */
	function __construct($path) {
		if ($path[strlen($path)-1] != '/') {
			$path .= '/';
		}
		$config = self::load($path . 'config.php', TRUE);
		$configHost = self::load($path . 'config.host.php', FALSE);
		$this->config = self::mergeArrays($config, $configHost);
	}

	private static function load($fileName, $required) {
		if (!is_file($fileName)) {
			if ($required) {
				throw new Exception("�� ������ ���� '$fileName'");
			} else {
				return array();
			}
		}
		require($fileName);
		if (gettype($config) != 'array') {
			throw new Exception("���� '$fileName' ������ ��������� ������������ ������ \$config");
		}
		return $config;
	}

	/**
	 * XXX ����� ������������� �� ������ SotmarketUtil.
	 */
	private static function copyArray(array &$a) {
		$result = array();
		foreach($a as $key => $value) {
			$result[$key] = (gettype($value) == 'array') 
					? self::copyArray($value) 
					: $value;
		}
		return $result;
	}
	
	/**
	 * XXX ����� ������������� �� ������ SotmarketUtil.
	 */
	private static function mergeArrays(array &$a1, array &$a2) {
		$result = self::copyArray($a1);
		foreach($a2 as $key => $value) {
			if (gettype($value) == 'array') {
				$result[$key] = (array_key_exists($key, $result) && gettype($result[$key]) == 'array') 
						? self::mergeArrays($result[$key], $value)
						: self::copyArray($value);
			} else {
				$result[$key] = $value;
			}
		}
		return $result;
	}
}
?>