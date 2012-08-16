<?php

class SotmarketOrder {

    protected $sOrderPage;
    protected $oOrder;

    function __construct($sOrderPage){

        $this->sOrderPage = $sOrderPage;

        if (isset($_SESSION['oOrder'])) {
            $this->oOrder = $_SESSION['oOrder'];
        } else {
            $this->oOrder = new RPCAPIPackage_Order();
        }
    }

    public function getCart($isAjax = false, $bIsCheckOut = false){

        $sReturn = '';
        if (!$isAjax){
            $sReturn .= '<link href="engine/modules/sotmarket/css/cart.css" type="text/css" rel="stylesheet" />';
            $sReturn .= '<script src="engine/modules/sotmarket/js/mag.js" type="text/javascript"></script>';
            $sReturn .= '<div id="cart">';

            $sProducts = $this->getProductsHtml($bIsCheckOut);
            $aTemplate = array();
            $aTemplate['sProducts'] = $sProducts;
            $aTemplate['iTotal'] = $this->oOrder->iProductsTotal;
            $aTemplate['sCartUrl'] = $this->sOrderPage;
            if ($bIsCheckOut){

            } else {
                $sReturn .= $this->render('cart',$aTemplate);
            }

            $sReturn .= '</div>';

        } else {

        }

        return $sReturn;
    }

    protected function getProductsHtml( $bIsCheckout = false ){

        $sProducts = '';
        $sTemplate = '';
        if ($bIsCheckout){
            $sTemplate = 'product_checkout';
        } else {
            $sTemplate = 'product';
        }

        $this->oOrder->iProductsTotal = 0;
        foreach($this->oOrder->aProducts as $aProduct){
            $this->oOrder->iProductsTotal += $aProduct['price'] * $aProduct['quantity'];
            $sProducts .= render($sTemplate,$aProduct);
        }

        return $sProducts;
    }

    protected function render($sViewName,$aParams = array()){

        extract($aParams);
        include('order_templates/' . $sViewName .'.php');
    }
}