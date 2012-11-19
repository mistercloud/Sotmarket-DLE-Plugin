<?php

include(ENGINE_DIR . '/api/api.class.php');


$result = $db->super_query("SELECT 1 AS c FROM " . PREFIX . "_admin_sections WHERE name='sotmarket'");

if (!$result['c'] || isset($_POST['uninstall'])) {
	include_once(ENGINE_DIR . '/modules/sotmarket/install.php');
	die();
}

include_once(ENGINE_DIR . '/modules/sotmarket/lang/russian.php');

$sSotmarketPluginUrl = substr( $config['http_home_url'] ,0 , -1 ) .'/admin.php?mod=sotmarket';
$sSotmarketTemplatesPath = ROOT_DIR  .'/templates/' . $config['skin'] . '/sotmarket/';
$aTemplateFiles = glob($sSotmarketTemplatesPath.'*.tpl');
$aTemplateFiles = str_replace( array($sSotmarketTemplatesPath,'.tpl'),'',$aTemplateFiles );

$sServerIp = $_SERVER['SERVER_ADDR'];


$bUpdated = false;
echoheader('Sotmarket Module', $aTranslations['MODULE_TITLE']);

if (isset($_POST['aSettings'])){
    foreach ($_POST['aSettings'] as $key => $val) {
        if (is_array($val)) {
            $val = implode(',', $val);
        }
        $res = $db->query("REPLACE INTO `" . PREFIX . "_sotmarket_settings` SET sm_key = '$key' , sm_value ='$val'");
        $bUpdated = true;
    }
}

if ($_GET['template']){
    $sTemplateName = $_GET['template'];
    if (!in_array($sTemplateName,$aTemplateFiles)){
        echo 'Шаблон не найден';
        echofooter();
        exit;
    }

    $sTemplateContent = htmlspecialchars(file_get_contents( $sSotmarketTemplatesPath . $sTemplateName . '.tpl'), ENT_QUOTES);
    echo <<<END
			<h3>Редактирование шаблона</h3>
			<a href="$sSotmarketPluginUrl">&laquo; вернуться к настройкам</a><br /><br />
        	<form method="post" action="$sSotmarketPluginUrl">
                <input type="hidden" name="hidden_send_template" value="Y">
                <input type="hidden" name="hidden_template_name" value="$sTemplateName" >
END;


    if (isset($_GET['new'])){
        echo '<label>Название (в одно слово, латиницей)</label><br/>';
        echo '<input style="width:400px" name="new_template_name" value="'. $sTemplateName . 'custom"><br/>';
    }
    echo <<<END
                <label><strong>Шаблон:</strong></label><br/>
                <textarea rows="10" cols="120" name="template-code">$sTemplateContent</textarea>
                <p class="submit">
					<input type="submit" name="Submit" value="Сохранить" class="btn btn-success" />
				</p>
			</form>
			<hr />
			<h3>Теги допустимые для использования в шаблоне</h3>
			<div>
				<p>Перечень тегов для вставки в шаблон:<br/>

				{id} - id товара <br/>
		        {url} - Ссылка на страницу с описанием товара на сайте sotmarket.ru с сохранением REF ссылки. <br/>
                {title} - название товара <br/>
                {image_src} - Путь к изображению товара <br/>
                {price} - цена товара <br/>
                {old_price} - Цена до распродажи <br/>
                {sale} - текст SALE, в этой переменной только если по товару идет распродажа<br/>


				</p>
			</div>
			<div>
				<a href="$sSotmarketPluginUrl&template=$sTemplateName&new=1">Сделать новый шаблон на основе этого</a>
			</div>
END;
    echofooter();
    exit;
}

//проверяем посылку запроса на изменение шаблона
if ($_POST['hidden_send_template'] == 'Y') {

    if ( isset( $_POST['new_template_name'] ) ){
        $sTemplateName = $_POST['new_template_name'];
    } else {
        $sTemplateName = $_POST['hidden_template_name'];
    }

    $sTemplateContent = $_POST['template-code'];
    //зачем то wp экранирует символы
    $sTemplateContent = str_replace('\"','"',$sTemplateContent);
    $sTemplateContent = str_replace("\'","'",$sTemplateContent);

    file_put_contents($sSotmarketTemplatesPath . $sTemplateName . '.tpl',$sTemplateContent);
    echo '<div class="updated"><p><strong>Шаблон обновлен</strong></p></div>';
}



$db->query("SELECT * FROM `" . PREFIX . "_sotmarket_settings`");

$aSettings = array();
while ($row = $db->get_row()) {
	$aSettings[$row['sm_key']] = $row['sm_value'];
}

?>
<script src="<?= substr( $config['http_home_url'] ,0 , -1 ) ?>/engine/skins/tabset.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function(){
        $("#tabsetsotmarket").buildMbTabset({
            sortable:false,
            position:"left"
        });

    });
</script>
<div id="tabsetsotmarket" class="tabset mbTabset left">
    <div>
        <a content="cont_1">Настройки</a>
        <a content="cont_2">Шаблоны</a>
        <a  content="cont_3">Документация и помощь</a>
    </div>
    <div>
        <div id="cont_1">
            <strong>IP сайта:</strong> <?= $sServerIp ?>         
<table>            
            
            <form method='POST'>
                <?php
                foreach ($aMandatoryParams as $sKey => $sName) {
                    if (isset($aMandatoryParamsAsSelect[$sKey])) {
                        echo "<tr><td style=\"padding:2px;\" nowrap>$sName:</td><td style=\"padding:2px;\" width=\"100%\"><select name='aSettings[$sKey]' class=\"edit bk\">";
                        foreach ($aMandatoryParamsAsSelect[$sKey] as $sKeyValue => $sKeyTitle) {
                            echo "<option value='$sKeyValue' " . ((@$aSettings[$sKey] == $sKeyValue) ? "selected='selected'>" : ">") . "$sKeyTitle</option>";
                        }
                        echo "</select></td></tr>";
                        continue;
                    } elseif (isset($aMandatoryParamsAsCheckBox[$sKey])) {
                        echo "<tr><td style=\"padding:2px;\" nowrap>$sName:</td><td style=\"padding:2px;\" width=\"100%\">";
                        foreach ($aMandatoryParamsAsCheckBox[$sKey] as $sKeyValue => $sKeyTitle) {
                            echo "<input type='checkbox' name='aSettings[$sKey][]' value='$sKeyValue' " . ((strpos(@$aSettings[$sKey], $sKeyValue) !== false) ? "checked='true'>" : ">") . "$sKeyTitle";
                        }
                        echo "</td></tr>";
                        continue;
                    }
                    echo "<tr><td style=\"padding:2px;\" nowrap>$sName:</td><td style=\"padding:2px;\" width=\"100%\"><input type='text' name='aSettings[$sKey]' value = '" . @$aSettings[$sKey] . "'></td></tr>";
                }
                echo "<tr><td style=\"padding:2px;\" colspan=\"2\"><input type='submit' value='$aTranslations[SETTINGS_UPDATE]' class=\"btn btn-success\"></td>
    </tr>";
                if ($bUpdated) {
                    echo "<div style='text-align:center; color: red'>$aTranslations[SETTINGS_UPDATED]</div>";
                }
                ?>
            </form>
</table>            
 
        </div>
        <div id="cont_2">
        <strong>Доступные шаблоны модуля sotmarket:</strong><br /><br />
            <?

            foreach( $aTemplateFiles as $sTemplate ){
                echo ' - <a href="' . $sSotmarketPluginUrl. '&template=' . $sTemplate . '">' . $sTemplate . '</a><br/>';
            }
            ?>
        </div>
        <div id="cont_3">
        Документация и инструкции, удаление модуля.<br /><br />
<strong class='bbc'>Полезная информация:</strong><br />
- <a href='http://partner.sotmarket.ru/forum2/index.php?/topic/23-%d0%b2%d1%81%d1%82%d0%b0%d0%b2%d0%ba%d0%b0-%d0%b8%d0%bd%d1%84%d0%be%d0%b1%d0%bb%d0%be%d0%ba%d0%be%d0%b2-%d0%b2-%d0%bd%d0%be%d0%b2%d0%be%d1%81%d1%82%d0%b8-%d0%b8-%d1%81%d1%82%d0%b0%d1%82%d0%b8%d1%87%d0%b5%d1%81%d0%ba%d0%b8%d0%b5-%d1%81%d1%82%d1%80/' class='bbc_url' title=''>Вставка инфоблоков в новости и статические страницы</a><a href='http://partner.sotmarket.ru/forum2/index.php?/topic/23-%d0%b2%d1%81%d1%82%d0%b0%d0%b2%d0%ba%d0%b0-%d0%b8%d0%bd%d1%84%d0%be%d0%b1%d0%bb%d0%be%d0%ba%d0%be%d0%b2-%d0%b2-%d0%bd%d0%be%d0%b2%d0%be%d1%81%d1%82%d0%b8-%d0%b8-%d1%81%d1%82%d0%b0%d1%82%d0%b8%d1%87%d0%b5%d1%81%d0%ba%d0%b8%d0%b5-%d1%81%d1%82%d1%80/' class='bbc_url' title=''> </a><br />
- <a href='http://partner.sotmarket.ru/forum2/index.php?/topic/21-%d0%b8%d1%81%d0%bf%d0%be%d0%bb%d1%8c%d0%b7%d0%be%d0%b2%d0%b0%d0%bd%d0%b8%d0%b5-%d1%88%d0%b0%d0%b1%d0%bb%d0%be%d0%bd%d0%be%d0%b2-%d0%b4%d0%bb%d1%8f-%d0%b2%d1%8b%d0%b2%d0%be%d0%b4%d0%b0-%d0%b8%d0%bd%d1%84%d0%be%d0%b1%d0%bb%d0%be%d0%ba%d0%be%d0%b2/' class='bbc_url' title=''>Использование шаблонов для вывода инфоблоков</a><br />
- <a href='http://partner.sotmarket.ru/forum2/index.php?/topic/19-%d0%b3%d0%b5%d0%bd%d0%b5%d1%80%d0%b0%d1%82%d0%be%d1%80-%d1%82%d0%b5%d0%b3%d0%be%d0%b2-%d0%b4%d0%bb%d1%8f-%d1%88%d0%b0%d0%b1%d0%bb%d0%be%d0%bd%d0%b0-cms-dle/' class='bbc_url' title=''>Генератор тегов для шаблона CMS DLE</a><a href='http://partner.sotmarket.ru/forum2/index.php?/topic/19-%d0%b3%d0%b5%d0%bd%d0%b5%d1%80%d0%b0%d1%82%d0%be%d1%80-%d1%82%d0%b5%d0%b3%d0%be%d0%b2-%d0%b4%d0%bb%d1%8f-%d1%88%d0%b0%d0%b1%d0%bb%d0%be%d0%bd%d0%b0-cms-dle/' class='bbc_url' title=''> </a><br />
- <a href='http://partner.sotmarket.ru/forum2/index.php?/topic/25-%d0%b2%d1%81%d1%82%d0%b0%d0%b2%d0%ba%d0%b0-%d0%b8%d0%bd%d1%84%d0%be%d0%b1%d0%bb%d0%be%d0%ba%d0%be%d0%b2-%d0%b2-%d1%88%d0%b0%d0%b1%d0%bb%d0%be%d0%bd%d1%8b-%d1%81%d0%b0%d0%b9%d1%82%d0%b0/' class='bbc_url' title=''>Вставка инфоблоков в шаблоны сайта</a><a href='http://partner.sotmarket.ru/forum2/index.php?/topic/25-%d0%b2%d1%81%d1%82%d0%b0%d0%b2%d0%ba%d0%b0-%d0%b8%d0%bd%d1%84%d0%be%d0%b1%d0%bb%d0%be%d0%ba%d0%be%d0%b2-%d0%b2-%d1%88%d0%b0%d0%b1%d0%bb%d0%be%d0%bd%d1%8b-%d1%81%d0%b0%d0%b9%d1%82%d0%b0/' class='bbc_url' title=''> </a>
					
					<br /><br />
            <form method = "POST">
                <input type = "submit" name = "uninstall" value = "Удалить модуль" class="btn btn-success" />
            </form>
        </div>
    </div>
</div>
<?php

echofooter();
