<?php
/**
 * Универсальная вещь. Используется всюду (в т.ч. как базовый класс), где нужно передавать 
 * множество сообщений об ошибках + предупреждений, или только предупреждений без ошибок.
 * 
 * Также используется как базовый класс для исключений, маршаллируемых RPC без
 * изменений (все остальные исключения заменяются на "Внутренняя ошибка сервера").
 */
class SotmarketException extends Exception {
	
	public $errors = array();
	public $warnings = array();
	
	/**
	 * @param mixed $errors Строка, массив строк или NULL.
	 * @param mixed $warnings Строка, массив строк или NULL.
	 */
	function __construct($errors, $warnings = NULL) {
		$this->fill($errors, $this->errors);
		$this->fill($warnings, $this->warnings);
		parent::__construct($this->createMessage());
	}

	function hasErrors() {
		return count($this->errors > 0);
	}

	function hasWarnings() {
		return count($this->warnings > 0);
	}

	private function fill($from, &$to) {
		if (gettype($from) == 'array') {
			foreach($from as $message) {
				$this->fill($message, $to);
			}
		} else if (gettype($from) == 'string' && strlen(trim($from)) > 0) {
			$to[] = $from;
		}
	}
	
	private function createMessage() {
		return trim(implode("\n", $this->errors) . "\n" . implode("\n", $this->warnings));
	}
}
?>