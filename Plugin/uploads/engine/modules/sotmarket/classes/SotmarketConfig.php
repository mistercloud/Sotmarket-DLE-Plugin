<?php
/**
 * ЗАМЕЧАНИЕ. предыдущая версия была синглтоном, но это не катит 
 * с точки зрения возможности совместной инсталляции клиентского 
 * и серверного кода. Поэтому доступ к настройкам теперь выполняется
 * через класс приложения (SPI).
 */
final class SotmarketConfig {

	/**
	 * @var array
	 */
	public $config;

	/**
	 * Загружает и мержит настройки из файлов $path/config.php и 
	 * $path/config.host.php в $this->config. Оба файла, если существуют, 
	 * должны объявлять НЕглобальный ассоциативный массив $config.
	 * Файл config.host.php является необязательным.
	 *
	 * @param string $path С хвостовым слешем или без.
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
				throw new Exception("Не найден файл '$fileName'");
			} else {
				return array();
			}
		}
		require($fileName);
		if (gettype($config) != 'array') {
			throw new Exception("Файл '$fileName' должен объявлять НЕглобальный массив \$config");
		}
		return $config;
	}

	/**
	 * XXX Метод продублирован из класса SotmarketUtil.
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
	 * XXX Метод продублирован из класса SotmarketUtil.
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