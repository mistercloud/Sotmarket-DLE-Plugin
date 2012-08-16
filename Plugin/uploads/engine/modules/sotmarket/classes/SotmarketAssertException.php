<?php
final class SotmarketAssertException extends Exception {

	function __construct($file, $line, $code) {
		$msg = "Assertion Failed\nFILE: $file($line)\nCODE: $code\n";
		parent::__construct($msg);
	}
}
?>