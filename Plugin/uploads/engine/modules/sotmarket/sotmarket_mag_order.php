<?php
function sotmarket_session() {
    if (!session_id()) {
        session_start();
    }
}
sotmarket_session();

require_once(dirname(__FILE__) . "/include.php");

$db->query("SELECT sm_value, sm_key FROM `" . PREFIX . "_sotmarket_settings`");

$aConfig = array();

while ($aRow = $db->get_array()) {
    $aConfig[$aRow['sm_key']] = $aRow['sm_value'];
}

$sCheckoutUrl = $_SERVER['REQUEST_URI'];
$iSiteId = (int)$aConfig['SOTMARKET_SITE_ID'];

$oConfig = new SotmarketConfig(dirname(__FILE__) . "/");
$oConfig->config['rpc']['site_id'] = $iSiteId;

$callback = new SotmarketRPCClientCallbackImp( $iSiteId , session_id());
$RPC_Client = new SotmarketRPCClient($oConfig->config['rpc'], $callback);
$oController = $RPC_Client->getObjectByClassName('SotmarketRPCOrder');
if (!isset($_SESSION['oOrder'])) {
    echo <<<END
    <div style="text-align;center;width:98%;margin:50px;">
    Ваша корзина пуста.<br />
    Добавьте товары в корзину и начните оформление заказа<br />
    <a href="/">Вернуться на главную</a>
    </div>
END;
    return;
}
$oOrder = $_SESSION['oOrder'];
$oOrder->OrderID = null;
$sCities = '';
$errMess = '';
$sDeliverInfo = '';

try {
    foreach ($_REQUEST as $sName => $sValue) {
        switch ($sName) {
            case 'aProducts':
                break;
            case 'phone_contact':
                $oOrder->$sName = '7' . $sValue;
                break;
            case 'FirstName_user':
            case 'LastName_user':
            case 'Street_address':
                $oOrder->$sName = iconv('utf-8','cp1251',$sValue);
                break;
            default:
                $oOrder->$sName = $sValue;
        }
    }
    $aCities = $oController->get_cities_list_cached();

    $sCities = '<span>Выберите Город:</span><select name="CityID_address" onChange="return requestAddressForm()"><option value="">-выберите город-</option>';
    $iCityId = $oOrder->CityID_address;

    foreach ($aCities as $ilCityId => $aCity) {
        $sCities .= '<option value="' . $ilCityId . '"' . (($iCityId == $ilCityId) ? " selected='selected'"
            : '') . '>' . iconv('cp1251','utf-8',$aCity['title']) . '</option>';
    }
    $sCities .= '</select><br><div id="address">';

    if (isset($aCities[$iCityId])) {
        if (!empty($aCities[$iCityId]['undeground'])) {
            $aCityMetro = $oController->get_stations_list_cached($iCityId);
            $sCities .= '<span>Выберите Метро:</span><select name="UndergroundID_address"><option value="">-выберите метро-</option>';
            $iMetroId = $oOrder->UndergroundID_address;
            foreach ($aCityMetro as $iMetroId => $aMetro) {
                $sCities .= '<option value="' . $iMetroId . '"' . (($iMetroId == $oOrder->UndergroundID_address)
                    ? " selected='selected'" : '') . '>' . $aMetro['title'] . '</option>';
            }
            $sCities .= '</select><br>';
        }
        if (!empty($aCities[$iCityId]['postcode'])) {
            $sCities .= '<span>Почтовый индекс:</span><input type="text" name="PostCode_address" value="' . $oOrder->PostCode_address . '"><br />';
        }
        if ($iCityId == 1) {
            $sCities .= '<span>Город:</span><input type="text" name="City_address" value="' . $oOrder->City_address . '"><br />';
        }
        $sCities .= '<span>Адрес доставки:</span><textarea name="Street_address">' . $oOrder->Street_address . '</textarea><br />';
    } else {
        $iCityId = 0;
    }
    $sCities .= '</div>';

    if (!isset($oOrder->user->Addresses[0])) {
        //echo '<pre>';
        // print_r($oOrder);
        // echo '</pre>';
        $oOrder->user->Addresses[0] = new RPCPackage_CustomerAddress();
        //print_r($oOrder->user);
        //die();
        //$oOrder->user->Addresses[0]->Validate();
    }


    // delivery
    $aDeliveryInfo = $oController->get_address_info_cached($oOrder->user->Addresses[0], 0, 0);
    $sDeliverInfo = '';
    foreach ($aDeliveryInfo['delivery_types'] as $sdt_name => $aInfo) {
        $sDeliverInfo .= '<div class="deliver">';
        $sDeliverInfo .= '<input type="radio" class="deliver" name="delivery_type" value="' . $sdt_name . '">&nbsp;' . iconv('cp1251','utf-8',$aInfo['name']) . '&nbsp;';
        if ($aInfo['price'] != '0') {
            $sDeliverInfo .= '<span style="color:red;font-weight:bold;">' . $aInfo['price'] . '</span><br />';
        } else {
            $sDeliverInfo .= '<span style="color:red;font-weight:bold;">бесплатно</span><br />';
        }
        $sDeliverInfo .= @$aInfo['com'];
        $sDeliverInfo .= '<div id="' . $sdt_name . '" class="payment hide">';
        foreach ($aInfo['payment_types'] as $sPaymentType) {
            $sDeliverInfo .= '<input type="radio" class="payment" name="payment_type" value="' . $sPaymentType . '">' . iconv('cp1251','utf-8',$aDeliveryInfo['payment_types'][$sPaymentType]['title']);
            if (!empty($aDeliveryInfo['payment_types'][$sPaymentType]['com'])) {
                $sDeliverInfo .= '<span class="hide" id="' . $sPaymentType . '">' . $aDeliveryInfo['payment_types'][$sPaymentType]['com'] . '</span>';
            }
            $sDeliverInfo .= '<br />';
        }
        $sDeliverInfo .= '</div></div>';
    }
    if (!isset($_REQUEST['submit_order'])) {
        // просто пустой эксепшн
        // мы первый раз зашли на эту страницу, поэтому проверим наличие товара на сервере
        $callback = new SotmarketRPCClientCallbackImp($oConfig->config['affiliate_id'], session_id());
        $oTestRPC = new SotmarketRPCClient($oConfig->config['rpc'], $callback);
        $aIds = array();
        foreach ($oOrder->aProducts as $key => $aProduct) $aIds[] = $aProduct['id'];
        $aArrayInfo = $oController->product_info_array($aIds);
        $errMess .= $oOrder->sUpdateProductsWithServerData($aArrayInfo);
        throw new InfoException('');
    }
    $oOrder->Validate();
    $oOrder1 = clone $oOrder;
    foreach ($oOrder1->aProducts as $key => $val) {
        $oOrder1->aProducts[$key]['title'] = null;
    }
    $oOrder1 = $oController->make_order_object($oOrder1);
    $oOrder->OrderID = $oOrder1->OrderID;

    $errMess = iconv('utf-8','cp1251',"Заказ создан с номером:") . $oOrder1->OrderID;
}
catch (Exception $e) {
    $errMess .= $e->getMessage();
}
?>
<script type="text/javascript">
    function getDeliveryType(){
        return '<?= $oOrder->delivery_type;?>';
    }
    function getPaymentType(){
        return '<?= $oOrder->payment_type;?>';
    }

</script>
<link href="engine/modules/sotmarket/css/mag_order.css" type="text/css" rel="stylesheet" />
<script src="engine/modules/sotmarket/js/mag_order.js" type="text/javascript"></script>
<div><?php echo iconv('cp1251','utf-8',$errMess);?></div>
<div id="sm_order">
    <form action="<?php
    echo $sCheckoutUrl . "\" method=\"POST\">";
    ?>
    <fieldset title=" Товары
    ">
    <legend>Товары</legend>
    <div id="cart_checkout">
    </div>
    </fieldset>
    <fieldset id="contacts" title="Контактная информация">
        <legend>Контактная информация</legend>
        <span>Имя:</span><input type="text" name="FirstName_user"
                                value="<?php echo iconv('cp1251','utf-8',$oOrder->FirstName_user )?>"><br/>
        <span>Фамилия:</span><input type="text" name="LastName_user"
                                    value="<?php echo iconv('cp1251','utf-8',$oOrder->LastName_user) ?>"><br/>
        <span>Телефон:</span><input type="text" name="phone_contact"
                                    value="+7<?php echo substr($oOrder->phone_contact, 3)?>"><br/>
        <span>E-mail:</span><input type="text" name="email_contact"
                                   value="<?php echo $oOrder->email_contact?>"><br/>
    </fieldset>
    <fieldset id="address"  title="Адрес">
        <legend>Адрес</legend>
        <?php echo $sCities;?>
        <input type="submit" onClick="return requestDelivery()" value="Перейти к выбору способу оплаты">
    </fieldset>
    <fieldset id="delivery" title="Доставка/Оплата"<?php echo (empty($sDeliverInfo)) ? " class='hide'" : ""; ?>>
        <legend>Доставка/Оплата</legend>
        <?php echo $sDeliverInfo ?>
    </fieldset>
    <input type="submit" name="submit_order" value="Оформить заказ">
    </form>
</div>