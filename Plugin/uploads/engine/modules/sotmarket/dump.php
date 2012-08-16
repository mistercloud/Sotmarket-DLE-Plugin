<?php
// Для отладки.
function dump($var, $title = NULL) {
	if ($title) {
		echo "<h3>" . htmlspecialchars($title) . "</h3>";
	}
	if (extension_loaded('xdebug')) {
		// Предполагается, что включена опция xdebug.overload_var_dump (она там по умолчанию).
		var_dump($var);
	} else {
		ob_start();
		var_dump($var);
		echo "<pre>" . htmlspecialchars(ob_get_clean()) . "</pre>";
	}
}

function dumptrace($title = NULL) {
	if ($title) {
		echo "<h3>" . htmlspecialchars($title) . "</h3>";
	}
	ob_start();
	debug_print_backtrace();
	echo nl2br(htmlspecialchars(ob_get_clean()));	
}



function dumpfiledelete() {
	$fileName = SotmarketDumper::instance()->fileName;
	if ($fileName) {
		@unlink($fileName);
	}
}

function dumpfile($var, $title = NULL) {
	$fileName = SotmarketDumper::instance()->fileName;
	if (!$fileName) {
		return;
	}
	
	ob_start();
	if ($title) {
		echo $title . "\n";
	}
	//var_dump($var);
	print_r($var);
	echo "\n";
	file_put_contents($fileName, date('Y-m-d H:i:s ') . ob_get_clean(), FILE_APPEND);	
}

// XXX Шота мне подсказывает, шо тут шота задвоено. И вообще надо бы вынести всё в синглтон.
//     А уш если ваще по уму, то нужен спецформат, шобы trace был в каждой записи,
//     и спецвьювер, шобы var_dump показывал в виде дерева с collapsable nodes... 
//     в общем нахуй.
function dumpfiletrace() {
	$fileName = SotmarketDumper::instance()->fileName;
	if (!$fileName) {
		return;
	}

	ob_start();
	debug_print_backtrace();
	echo "\n";
	file_put_contents($fileName, date("Y-m-d H:i:s\n") . ob_get_clean(), FILE_APPEND);	
}
?>