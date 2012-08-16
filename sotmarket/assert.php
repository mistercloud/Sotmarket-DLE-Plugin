<?php
// TODO Вот теперь нужно всюду, где используется assert(), ловить исключения. Гы-гы.

function sotmarketAssertHandler($file, $line, $code) {
	throw new SotmarketAssertException($file, $line, $code);
}

assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_BAIL, 0);
assert_options(ASSERT_QUIET_EVAL, 1);
assert_options(ASSERT_CALLBACK, "sotmarketAssertHandler");
?>