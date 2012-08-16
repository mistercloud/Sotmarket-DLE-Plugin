<?php
/**
 *
 * @copyright   Copyright (c) 2011, SOTMARKET.RU
 * @version     0.1.4
 * @author      Andrey Smirnov
 */

header('Content-Type:text/html; charset=utf-8');

include_once(dirname(__FILE__) . "/include.php");

session_start();
define ( 'DATALIFEENGINE', true );
define ( 'ROOT_DIR', dirname ( __FILE__ ).'/../../..' );
define ( 'ENGINE_DIR', dirname ( __FILE__ ).'/../..' );
require_once ROOT_DIR . '/engine/init.php';
$db->query("SELECT sm_value, sm_key FROM `" . PREFIX . "_sotmarket_settings`");
$aConfig = array();
while ($aRow = $db->get_array()) {
    $aConfig[$aRow['sm_key']] = $aRow['sm_value'];
}

$iSiteId = (int)$aConfig['SOTMARKET_SITE_ID'];
$oConfig = new SotmarketConfig(dirname(__FILE__) . "/");
$oConfig->config['rpc']['site_id'] = $iSiteId;

$callback = new SotmarketRPCClientCallbackImp( $iSiteId , session_id());
$oConfig->config['rpc']['tmpPath'] = ENGINE_DIR."/modules/sotmarket/_data/tmp/";
$RPC_Client = new SotmarketRPCClient($oConfig->config['rpc'], $callback);
$oController = $RPC_Client->getObjectByClassName('SotmarketRPCOrder');

function render($sViewName,$aParams = array()){

    extract($aParams);
    include($sViewName);
}

if (isset($_SESSION['oOrder'])) {
    $oOrder = $_SESSION['oOrder'];
} else {
    $oOrder = new RPCAPIPackage_Order();
}
$sAction = @$_REQUEST['action'];
switch ($sAction) {
    case 'add':
        $sName = $_REQUEST['name'];
        if (!empty($_REQUEST['id']) && !empty($_REQUEST['cnt']))
            $oOrder->vAddProduct($_REQUEST['id'], $_REQUEST['cnt'], $_REQUEST['price'], $sName);
        break;
    case 'remove':
        $oOrder->vRemoveProduct($_REQUEST['id']);
        break;
    case 'update':
        $oOrder->vUpdateProduct($_REQUEST['id'], $_REQUEST['cnt']);
        break;
    case 'addressform':
        $iCityId = (int)$_REQUEST['cityID'];
        // id города не передан, выходим
        if (empty($iCityId)) exit;
        try {
            $aCities = $oController->get_cities_list_cached();
        } catch (InfoException $e) {
            echo iconv('cp1251','utf-8',$e->getMessage());
        }
        $aResponse = array();
        // города с таким id нет выходим
        if (!isset($aCities[$iCityId])) exit;
        // Метро!
        if (!empty($aCities[$iCityId]['undeground'])) {
            $aResponse['forms']['UndergroundID_address'] = 'Станция метро:';
            $aMetroList = $oController->get_stations_list_cached($iCityId);
            $aMetroList2 = $aMetroList3 = array();
            foreach ($aMetroList as $aMetro) $aMetroList2[$aMetro['metro_id']] = $aMetro['title'];
            asort($aMetroList2);
            $i = 0;
            foreach ($aMetroList2 as $iMetroId => $sMetro) {
                $aMetroList3[$i++] = array('id' => $iMetroId, 'title' => $sMetro);
            }
            $aResponse['metro'] = $aMetroList3;
        }
        // для адреса необходим индекс
        if (!empty($aCities[$iCityId]['postcode'])) {
            $aResponse['forms']['City_address'] = 'Почтовый индекс:';
        }
        // Выбрана "Россия" Нужно указать город
        if ($iCityId == 1) {
            $aResponse['forms']['PostCode_address'] = 'Город:';
        }
        $aResponse['forms']['Street_address'] = 'Адрес доставки:';
        echo json_encode($aResponse);
        exit;
        break;
    case 'delivery':
        $sPath = dirname(__FILE__);

        $oAddress = new RPCPackage_CustomerAddress();
        $oAddress->CityID = (int)$_REQUEST['cityID'];
        $oAddress->City = $_REQUEST['city'];
        $oAddress->PostCode = $_REQUEST['postcode'];
        $oAddress->Street = $_REQUEST['street'];
        $oAddress->UndergroundID = $_REQUEST['undgr'];
        try {
            //$oAddress->Validate();
            $aDeliveryInfo = $oController->get_address_info_cached($oAddress, 0, 0);
            $sDeliverInfo = '';
            foreach ($aDeliveryInfo['delivery_types'] as $sdt_name => $aInfo) {
                $sDeliverInfo .= '<div class="deliver">';
                $sDeliverInfo .= '<input type="radio" class="deliver" name="delivery_type" value="' . $sdt_name . '">' . $aInfo['name'] . '&nbsp;';
                if ($aInfo['price'] != '0') {
                    $sDeliverInfo .= '<span style="color:red;font-weight:bold;">' . $aInfo['price'] . '</span><br />';
                } else {
                    $sDeliverInfo .= '<span style="color:red;font-weight:bold;">'. iconv('utf-8','cp1251','бесплатно') .'</span><br />';
                }
                $sDeliverInfo .= @$aInfo['com'];
                $sDeliverInfo .= '<div id="' . $sdt_name . '" class="payment hide">';
                foreach ($aInfo['payment_types'] as $sPaymentType) {
                    $sDeliverInfo .= '<input type="radio" class="payment" name="payment_type" value="' . $sPaymentType . '">' . $aDeliveryInfo['payment_types'][$sPaymentType]['title'];
                    if (!empty($aDeliveryInfo['payment_types'][$sPaymentType]['com'])) {
                        $sDeliverInfo .= '<span class="hide" id="' . $sPaymentType . '">' . $aDeliveryInfo['payment_types'][$sPaymentType]['com'] . '</span>';
                    }
                    $sDeliverInfo .= '<br />';
                }
                $sDeliverInfo .= '</div></div>';
            }
            echo json_encode(array('result' => iconv('cp1251','utf-8',$sDeliverInfo), 'status' => 1));
        } catch (Exception $e) {
            echo json_encode(array('result' => iconv('cp1251','utf-8',$e->getMessage()), 'status' => 4));
        }
        exit;
}

$sCartId = isset($_REQUEST['cartId']) ? $_REQUEST['cartId'] : 'cart';
if ($sCartId != 'cart' && $sCartId != 'cart_checkout'){
    $sCartId = 'cart';
}
$sCartUrl = $_REQUEST['cart_url'];
$aTemplate = array();
$aTemplate['iTotal'] = '';
$aTemplate['sProducts'] = '';
$aTemplate['sCartUrl'] = $sCartUrl;

foreach($oOrder->aProducts as $aProduct){
    $aTemplate['iTotal'] += $aProduct['price'] * $aProduct['quantity'];
    if ($sCartId == 'cart'){
        $aTemplate['sProducts'] .= render('cart_template/product.php',$aProduct);
    }
    if ($sCartId == 'cart_checkout'){
        $aTemplate['sProducts'] .= render('cart_template/product_checkout.php',$aProduct);
    }


}
if ($sCartId == 'cart'){
    $sReturn = render('cart_template/cart.php',$aTemplate);
}
if ( $sCartId == 'cart_checkout'){
    $sReturn = render('cart_template/cart_checkout.php',$aTemplate);
}
echo $sReturn;
/*echo json_encode(array('products' => $oOrder->aProducts, 'cartId' => isset($_REQUEST['cartId']) ? $_REQUEST['cartId']
                         : ''));*/

$_SESSION['oOrder'] = $oOrder;