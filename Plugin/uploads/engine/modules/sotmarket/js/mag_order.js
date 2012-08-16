var delivery_type = getDeliveryType();
var payment_type = getPaymentType();
jQuery().ready(function() {
    //showDelivery(delivery_type, payment_type);
    refreshCart('cart_checkout');
});