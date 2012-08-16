jQuery().ready(function() {

    refreshCart('cart');

    jQuery("div.deliver input.deliver").click(function () {
        id = jQuery(this)[0].value;
        sRadioSelected = jQuery("input[name='payment_type']:checked").val();
        showDelivery(id, sRadioSelected);
    });
    jQuery("div.deliver input.payment").click(function () {
        id = jQuery(this)[0].value;
        jQuery("div.payment span").addClass('hide');
        jQuery("div.payment span").removeClass('show');
        jQuery("div.payment span#" + id).removeClass('hide');
        jQuery("div.payment span#" + id).addClass('show');
    });
})

site_url = getSiteUrl();
cart_url = getSiteUrl() + 'engine/modules/sotmarket/mag_ajax_cart.php';
checkout_url = getCheckoutUrl();
cart_type = 'cart';

function showDelivery(PaymentId, DeliveryId) {
    jQuery("div.payment").addClass('hide');
    jQuery("div.payment").removeClass('show');
    if (PaymentId == '') return;
    jQuery("div#" + PaymentId).removeClass('hide');
    jQuery("div#" + PaymentId).addClass('show');
    jQuery("input.deliver:radio").each(function() {
        if (jQuery(this).val() == PaymentId) {
            jQuery(this).attr('checked', 'checked');
        }
    })
    if (DeliveryId) {
        jQuery("div#" + PaymentId + " input:radio").each(function() {
            if (jQuery(this).val() == DeliveryId) {
                jQuery(this).attr('checked', 'checked');
            }
        })
    }
}




function addProductToCart(product_id, product_price, title) {
    if (product_id != null) {
        if (document.getElementById('cnt' + product_id))
            product_cnt = document.getElementById('cnt' + product_id).value;
        else
            product_cnt = 1;
    } else {
        product_cnt = 0;
    }
    jQuery.ajax({
        url:  cart_url,
        dataType: 'html',
        type: "POST",
        data: ({action:'add',id:product_id,cnt:product_cnt,price:product_price,name:title,cartId:'cart_checkout,cart'}),
        success: rebuildCart
    });
}
function updateProduct(product_id, product_cnt) {
    jQuery.ajax({
        url: cart_url,
        dataType: 'html',
        type: "POST",
        data: ({action:'update',id:product_id,cnt:product_cnt,cartId:'cart_checkout,cart'}),
        success: rebuildCart
    });
}
function removeProduct(product_id) {
    jQuery.ajax({
        url: cart_url,
        dataType: 'html',
        type: "POST",
        data: ({action:'remove',id:product_id,cartId:'cart_checkout,cart'}),
        success: rebuildCart
    });
}
function updateCart(obj) {
    updateProduct(obj.id.substr(3), obj.value);
}

function requestDelivery(){
    iCityId = jQuery("select[name='CityID_address'] option:selected").val()
    CityName = jQuery("input[name='City_address']").val()
    postcode = jQuery("input[name='PostCode_address']").val()
    street     = jQuery("textarea[name='Street_address']").val()
    iUndeground = jQuery("input[name='UndergroundID_address']").val()

    jQuery.ajax({
        url: cart_url,
        dataType: 'json',
        type: "POST",
        data: ({action:'delivery',cityID:iCityId,city:CityName,post:postcode,street:street,undgr:iUndeground}),
        success: updateDelivery
    });
    return false;
}
function updateDelivery(dataResponse){
    jQuery("#delivery").addClass('show');
    jQuery("#delivery").removeClass('hide');
    if (dataResponse['status'] == 1){
        jQuery('#delivery').html(dataResponse['result']);
        jQuery("div.deliver input.deliver").click(function () {
            id = jQuery(this)[0].value;
            sRadioSelected = jQuery("input[name='payment_type']:checked").val();
            showDelivery(id, sRadioSelected);
        });
        jQuery("div.deliver input.payment").click(function () {
            id = jQuery(this)[0].value;
            jQuery("div.payment span").addClass('hide');
            jQuery("div.payment span").removeClass('show');
            jQuery("div.payment span#" + id).removeClass('hide');
            jQuery("div.payment span#" + id).addClass('show');
        });
    }else{
        jQuery('#delivery').html(dataResponse['result']);
        requestAddressForm();
    }
    return false;
}
function requestAddressForm(){
    iCityId = jQuery("select[name='CityID_address'] option:selected").val()
    jQuery.ajax({
        url: cart_url,
        dataType: 'json',
        type: "POST",
        data: ({action:'addressform',cityID:iCityId}),
        success: updateAddressForm
    });
    return false;
}
function updateAddressForm(dataResponse){
// Получаем!
// dataResponse[forms]
// dataResponse[metro]
    if (!dataResponse['forms']) return;
    str = ''
    for (var sFormKey in dataResponse['forms']) {
        if (!dataResponse['forms'].hasOwnProperty(sFormKey)) continue;
        var sFormVal = dataResponse['forms'][sFormKey];
        str += '<span>' + sFormVal + '</span>'
        if (sFormKey == 'Street_address'){
            sCurrentValue = jQuery("textarea[name='Street_address']").val()
            if (sCurrentValue == undefined) sCurrentValue = ''
            str  += '<textarea name="Street_address">' + sCurrentValue + '</textarea>'
        }else if(sFormKey == 'UndergroundID_address'){
            iMetroId = jQuery("select[name='UndergroundID_address'] option:selected").val()
            str += '<select name="UndergroundID_address"><option value="">-выберите метро-</option>'
            for (iId in dataResponse['metro']) {
                if (!dataResponse['metro'].hasOwnProperty(iId)) continue;
                sMetroVal = dataResponse['metro'][iId]['title']
                sMetroKey = dataResponse['metro'][iId]['id']
                if (sMetroKey == iMetroId) sSel = ' selected=\'selected\''; else sSel = '';
                str += '<option value="' + sMetroKey + '"'+ sSel + '>' + sMetroVal + '</option>'
            }
            str += '</select>'
        }else{
            sCurrentValue =  jQuery("input[name='" + sFormKey + "']").val()
            if (sCurrentValue == undefined) sCurrentValue = ''
            str += '<input type="text" name="' + sFormKey + '" value="' + sCurrentValue + '">'
        }
        str += '<br />'
    }
    jQuery('#address').html(str)
}



function refreshCart(cartId) {
    if (typeof cartId == 'undefined') cartId = '';
    if (cartId == 'cart' ){
        jQuery.ajax({
            url:  cart_url,
            dataType: 'html',
            data: ({cartId:cartId , cart_url:checkout_url}),
            type: "POST",
            success: rebuildCart
        });
    }
    if (cartId == 'cart_checkout'){
        jQuery.ajax({
            url:  cart_url,
            dataType: 'html',
            data: ({cartId:cartId , cart_url:checkout_url}),
            type: "POST",
            success: rebuildCartCheckout
        });
    }
}

function rebuildCart(dataResponse) {

    jQuery('#cart' ).html(dataResponse);
    jQuery('#cart_url' ).attr('href',checkout_url);
}

function rebuildCartCheckout(dataResponse) {

    jQuery('#cart_checkout' ).html(dataResponse);

}
