<?php

if(!defined('DATALIFEENGINE')) die("Hacking attempt!");

$aTypes = array('products','related','analog');
if ( !isset($type) || (!isset($product_id) && !isset($product_name)) && !in_array($type,$aTypes)){
	echo 'Не установлен тип или товар';
	return;
}

include_once(dirname(__FILE__) . "/include.php");

$xfields = xfieldsload();

$aProductIds = array();
if ( !empty($product_id) ){
    $aProductIds = explodeProductId( $product_id );
	$product_id = trim($product_id);
	$aTmpProductIds = explode(',',$product_id);
	foreach($aTmpProductIds as $iProductId){
		$iProductId = (int) $iProductId;
		if ( $iProductId ){
			$aProductIds[] = $iProductId;
		}
	}
}

$sProductName = '';
if ( !$aProductIds && !empty($product_name)){
	$sProductName = trim($product_name);
}

$installed = $db->super_query("SHOW TABLES LIKE '" . PREFIX . "_sotmarket_settings'");
if (empty($installed)){
	echo "Установите модуль сотмаркет заново";
	return;
}
$db->query("SELECT sm_value, sm_key FROM `" . PREFIX . "_sotmarket_settings`");

$aConfig = array();

while ($aRow = $db->get_array()) {
	$aConfig[$aRow['sm_key']] = $aRow['sm_value'];
}

$aConfig['home_url'] = $config['http_home_url'];
// освобождаем db
$db->free();



$iCnt = 1;
if ( isset( $cnt ) ){
	$cnt = (int)$cnt;
	if ($cnt > 0){
		$iCnt = $cnt;
	}
}

$sImageSize = 'default';
if ( isset( $image_size) ){
	$aImageSizes = array(
		"100x100",
		"140x200",
		"300x250" ,
		"1200x1200",
		"100x150",
		"50x50",
		"default"
	);

	if ( in_array( $image_size, $aImageSizes ) ){
		$sImageSize = $image_size;
	}
}

$sTemplate = 'base';
if (isset($template)){
	$sTemplate = $template;
}

$aCategories = array();
if ( isset( $categories ) ) {
	$aTmpCategories = explode( ',',$categories );
	foreach( $aTmpCategories as $iCategory ){
		$iCategory = (int) $iCategory;
		if ($iCategory > 0 ){
			$aCategories[] = $iCategory;
		}
	}
}

$sFullstory = false;
if ( isset( $fullstory) ){
    if ($fullstory == "on"){
        //ищем id или имя товара по новости
        if (isset($_GET['newsid'])){
            $iNewsId = $_GET['newsid'];
            $sXFields = $db->super_query( "SELECT xfields FROM " . PREFIX . "_post WHERE id = ".$iNewsId );
            $sXFields = $sXFields['xfields'];
            $aXFields = xfieldsdataload($sXFields);
            if (isset($aXFields['sotmarket_product_id'])){
                $aProductIds = explodeProductId($aXFields['sotmarket_product_id']);
            } elseif (isset($aXFields['sotmarket_product_name'])){
                $sProductName = $aXFields['sotmarket_product_name'];
            }
        }
    }
}

if (isset($subref)){
    $aConfig['SOTMARKET_SUBREF'] = $subref;
}
$oSotmarketProduct = new SotmarketProduct( $aConfig ,$tpl , $type );
try {
	$sReturn = $oSotmarketProduct->getProducts( $aProductIds, $sProductName, $iCnt , $sTemplate, $sImageSize, $aCategories );
} catch (Exception $e) {
	$sReturn = $e->getMessage();
}

//проверка на доп поля в тексте
if( strpos( $sReturn, "[xfvalue_" ) !== false && isset($_GET['newsid'])){

    $iNewsId = $_GET['newsid'];
    $sXFields = $db->super_query( "SELECT xfields FROM " . PREFIX . "_post WHERE id = ".$iNewsId );
    $sXFields = $sXFields['xfields'];
    $aXFields = xfieldsdataload($sXFields);
    foreach($aXFields as $sFieldName => $sFieldValue){
        $sReturn = str_replace( "[xfvalue_{$sFieldName}]", stripslashes( $sFieldValue ), $sReturn );
    }

}

echo $sReturn;



