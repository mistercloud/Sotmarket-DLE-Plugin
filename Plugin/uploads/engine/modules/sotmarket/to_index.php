<?php

//если редирект
if (isset($_GET['srdr'])){
    $sUrl = urldecode(base64_decode($_GET['srdr']));
    header('Location: '.$sUrl);
    die();
}
$aMatches = array();
preg_match_all( "%\[sotmarket\].*?\[/sotmarket\]%si" , $tpl->result['main'], $aMatches);
foreach( $aMatches[0] as $sMatche){

    if (!strpos($sMatche,'sotmarket.ru/product') && !strpos($sMatche,'srdr=')){
        $tpl->result['main'] = str_replace($sMatche,'', $tpl->result['main'] );
    }
}
$tpl->result['main'] = str_replace('[sotmarket]','', $tpl->result['main'] );
$tpl->result['main'] = str_replace('[/sotmarket]','', $tpl->result['main'] );

