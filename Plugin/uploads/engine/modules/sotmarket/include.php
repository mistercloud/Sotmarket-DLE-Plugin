<?php

    function explodeProductId( $sProductIds ){
        $aProductIds = array();
        $sProductIds = trim($sProductIds);
        $aTmpProductIds = explode(',',$sProductIds);
        foreach($aTmpProductIds as $iProductId){
            $iProductId = (int) $iProductId;
            if ( $iProductId ){
                $aProductIds[] = $iProductId;
            }
        }

        return $aProductIds;
    }

	function sotmarketAutoload($className) 
	{
		$ArrPath = explode(PATH_SEPARATOR, get_include_path());

		for($i=0; $i<count($ArrPath); $i++)
		{
			if( is_file($ArrPath[$i] . "/" . $className . '.php') )
			{
				include_once($ArrPath[$i] . "/" . $className . '.php');	
			}
		}
	}
	
	spl_autoload_register('sotmarketAutoload');
	
	set_include_path(get_include_path() 
			. PATH_SEPARATOR . dirname(__FILE__) . '/classes'
			. PATH_SEPARATOR . dirname(__FILE__) . '/classes/packages'
	);
			
	require_once dirname(__FILE__) . '/assert.php';
	require_once dirname(__FILE__) . '/dump.php';
	require_once(dirname(__FILE__) . "/SotmarketProduct.php");

?>