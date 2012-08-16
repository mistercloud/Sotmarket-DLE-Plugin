<?php

include_once(dirname(__FILE__) . "/include.php");

$db->query("SELECT sm_value, sm_key FROM `" . PREFIX . "_sotmarket_settings`");

$aConfig = array();

while ($aRow = $db->get_array()) {
    $aConfig[$aRow['sm_key']] = $aRow['sm_value'];
}

$sCheckoutPageUrl = $aConfig['SOTMARKET_CART_URL'];
$sUrl            = '';

echo '
        <link href="engine/modules/sotmarket/css/cart.css" type="text/css" rel="stylesheet" />
        <script type="text/javascript">
		function getSiteUrl(){
			return "' . $sUrl . '";
		}
		function getCheckoutUrl(){
			return "' . $sUrl . $sCheckoutPageUrl . '";
		}
        </script>
        <script src="engine/modules/sotmarket/js/mag.js" type="text/javascript"></script>
		 ';
echo "<div id='cart'></div>";