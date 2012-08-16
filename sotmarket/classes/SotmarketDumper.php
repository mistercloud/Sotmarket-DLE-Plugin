<?php
final class SotmarketDumper {
	
	/**
	 * @return SotmarketDumper
	 */
	static function instance() {
		if (!self::$_instance) {
			self::$_instance = new SotmarketDumper();
		}
		return self::$_instance;
	}
	
	private static $_instance;
	
	public $fileName;

	function init(SotmarketConfig $config) {
		$this->fileName = @$config->config["dumpfile"]["fileName"];
	}
}
?>