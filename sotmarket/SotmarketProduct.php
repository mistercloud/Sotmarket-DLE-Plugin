<?php

class SotmarketProduct {

    //контроллер для запросов
    protected $oController;
    //кешировщик картинок
    protected $oImageCache;
    //объект шаблонизатора
    protected $oTpl;
    //путь до сайта
    protected $sHomeUrl;
    //id сайта
    protected $iSiteId;
    //id партнера
    protected $iPartnerId;

    //тип получаемых данных
    protected $sType;

    protected $sLabelType = 'from';
    protected $sLabel;

    protected $bExternalLink = true;

    protected $sBlockType = 'informer';

    function __construct( $aConfig , $oTpl , $sType ){

        $this->oTpl = $oTpl;
        //конфиг
        $this->iSiteId = $aConfig['SOTMARKET_SITE_ID'];
        $this->iPartnerId = $aConfig['SOTMARKET_PARTNER_ID'];
        $oConfig = new SotmarketConfig(dirname(__FILE__) . "/");
        $oConfig->config['rpc']['site_id'] = $this->iSiteId;

        $callback = new SotmarketRPCClientCallbackImp( $this->iSiteId , session_id());
        $RPC_Client = new SotmarketRPCClient($oConfig->config['rpc'], $callback);
        $this->oController = $RPC_Client->getObjectByClassName('SotmarketRPCOrder');

        $this->oImageCache = new SotmarketClientImageCacheFile($oConfig->config['rpc']);

        $this->sHomeUrl = $aConfig['home_url'];


        $aTypes = array('products','related','analog');
        if ( in_array( $sType,$aTypes ) ){
            $this->sType = $sType;
        } else {
            echo 'Неверный тип запрашиваемых данных';
        }

        $this->sLabel = $aConfig['SOTMARKET_FROM'];
        $this->sLabelType = $aConfig['SOTMARKET_LABEL_TYPE'];

        if ($aConfig['SOTMARKET_LINK_TYPE'] == 'redirect'){
            $this->bExternalLink = false;
        }

        $this->sBlockType = $aConfig['SOTMARKET_BLOCK_TYPE'];
    }

    public function getProducts( $aProductsIds, $sProductName = '', $iCnt = 1,  $sTemplate = 'sotmarket_block' , $sImageSize = 'default', $aCategories = array()){

        if ( $sProductName && !$aProductsIds){
            if ( $this->sType == 'products' && $aCategories){
                $aProductsIds = $this->oController->product_search_cached( $sProductName , $aCategories );
            } else {
                $aProductsIds = $this->oController->product_search_cached( $sProductName , array(11,25) );
            }

        }

        if ( !$aProductsIds ){
            return 'Не найдено товаров по данным критериям';
        }

        if ( $this->sType == 'products' ){
            array_splice( $aProductsIds, $iCnt );
        }

        if ( $this->sType == 'analog' ){
            if (!$aCategories ){
                $aCategories = array(11,25);
            }
            $aAnalogIds = array();
            foreach( $aProductsIds as $iProductId ){

                $aFindedAnalogProductIds = $this->oController->products_analog_cached( $iProductId, $aCategories,array());
                $aAnalogIds = array_merge( $aAnalogIds, $aFindedAnalogProductIds );
                //если уже получили больше товаров чем в пределе
                if ( count($aAnalogIds ) > $iCnt ){

                    array_splice($aAnalogIds, $iCnt );
                    break;
                }
            }

            $aProductsIds = $aAnalogIds;
        }

        if ( $this->sType == "related" ){

            $aRelatedIds = array();
            foreach( $aProductsIds as $iProductId ){
                $aFindedRelatedProductIds = $this->oController->product_accessories_cached( $iProductId, $aCategories,array());

                $aRelatedIds  = array_merge( $aRelatedIds , $aFindedRelatedProductIds );
                //если уже получили больше товаров чем в пределе
                if ( count( $aRelatedIds  ) > $iCnt ){

                    array_splice($aRelatedIds , $iCnt );
                    break;
                }
            }

            $aProductsIds = $aRelatedIds;
        }


        //находим информацию о товаре
        $aProductsInfo = $this->oController->product_info_array_cached($aProductsIds, array('image_size' => $sImageSize), true);

        $aProducts = array();
        foreach( $aProductsInfo as $iProductId => $aProductInfo ){

            $sImgUrl = $aProductsInfo[$iProductId]['image_url'];
            $sLocalImgUrl = 'img/' . $iProductId . '-' . $sImageSize . $this->oImageCache->sGetExtensionWithDot($sImgUrl);
            if (!$this->oImageCache->bCheckCache($sLocalImgUrl)) {
                if ($this->oImageCache->bSaveRemote($sLocalImgUrl, $sImgUrl)) {
                    $sFullImgUrl = $this->sHomeUrl.$this->oImageCache->sGetImagePath($sLocalImgUrl);

                } else {
                    $sFullImgUrl = $this->sHomeUrl.$this->oImageCache->sGetDefaultImagePath();
                }
            } else {
                $sFullImgUrl = $this->sHomeUrl.$this->oImageCache->sGetImagePath($sLocalImgUrl);

            }

            $aProductInfo['name'] = iconv( 'cp1251','utf-8',$aProductInfo['name'] );

            $sProductUrl = $aProductInfo['info_url'] . '?ref=' . $this->iPartnerId;
            if ($this->sLabel){
                $sProductUrl .= '&'.$this->sLabelType .'='.$this->sLabel;
            }

            if ( !$this->bExternalLink && $this->sBlockType == 'informer'){
                $sProductUrl = '/?srdr='.urlencode(base64_encode($sProductUrl));
            }

            //если тип - корзина
            $aProductInfo['full_url'] = '';
            $aProductInfo['script_url'] = '';
            if ( $this->sBlockType == 'shop' ){

                $aProductInfo['full_url'] = "<a href='" . $sProductUrl . "'>";
                $aProductInfo['script_url'] = "<a href='#' onClick='addProductToCart( " . $iProductId . ", " .
                    $aProductInfo['price'] . " , \"" . $aProductInfo['name']. "\"); alert(\"Товар добавлен в корзину\"); return false'>";


            }

            $aProducts[] = array(
                '{id}' => $iProductId,
                '{sale}' => $aProductInfo['isSale'] ? 'SALE&nbsp;' : '',
                '{title}' => $aProductInfo['name'],
                '{price}' => $aProductInfo['price'],
                '{url}' => $sProductUrl,
                '{image_src}' => $sFullImgUrl,
                '{full_url}' => $aProductInfo['full_url'],
                '{script_url}' => $aProductInfo['script_url'],

            );
        }

        $sReturn = $this->getRenderedData( $aProducts , $sTemplate);

        if ( $this->sBlockType == 'shop' ){
            foreach($aProducts as $aProduct ){
                $sReturn = str_replace( $aProduct['{full_url}'] , $aProduct['{script_url}'], $sReturn);
            }
        }

        return $sReturn;

    }

    protected function getRenderedData( $aProducts = array() , $sTemplate = 'sotmarket_block'){

        $this->oTpl->load_template('sotmarket/'. $sTemplate . '.tpl');
        foreach ($aProducts as $aProduct) {
            $this->oTpl->set('', $aProduct);
            $this->oTpl->compile('sotmarket_content');
        }
        $sReturn = $this->oTpl->result['sotmarket_content'];
        // очищаем шаблончег
        $this->oTpl->clear();

        return $sReturn;

    }
}