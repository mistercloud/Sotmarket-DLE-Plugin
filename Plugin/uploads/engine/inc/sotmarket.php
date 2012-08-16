<?php

include(ENGINE_DIR . '/api/api.class.php');


$result = $db->super_query("SELECT 1 AS c FROM " . PREFIX . "_admin_sections WHERE name='sotmarket'");

if (!$result['c'] || isset($_POST['uninstall'])) {
    include_once(ENGINE_DIR . '/modules/sotmarket/install.php');
    die();
}

include_once(ENGINE_DIR . '/modules/sotmarket/lang/russian.php');

$sSotmarketPluginUrl = '/admin.php?mod=sotmarket';
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
        echo '������ �� ������';
        echofooter();
        exit;
    }

    $sTemplateContent = htmlspecialchars(file_get_contents( $sSotmarketTemplatesPath . $sTemplateName . '.tpl'), ENT_QUOTES);
    echo <<<END
			<h3>�������������� �������</h3>
			<a href="$sSotmarketPluginUrl">&laquo; ��������� � ����������</a><br /><br />
        	<form method="post" action="$sSotmarketPluginUrl">
                <input type="hidden" name="hidden_send_template" value="Y">
                <input type="hidden" name="hidden_template_name" value="$sTemplateName" >
END;


    if (isset($_GET['new'])){
        echo '<label>�������� (� ���� �����, ���������)</label><br/>';
        echo '<input style="width:400px" name="new_template_name" value="'. $sTemplateName . 'custom"><br/>';
    }
    echo <<<END
                <label><strong>������:</strong></label><br/>
                <textarea rows="10" cols="120" name="template-code">$sTemplateContent</textarea>
                <p class="submit">
					<input type="submit" name="Submit" value="���������" class="btn btn-success" />
				</p>
			</form>
			<hr />
			<h3>���� ���������� ��� ������������� � �������</h3>
			<div>
				<p>�������� ����� ��� ������� � ������:<br/>

				{id} - id ������ <br/>
		        {url} - ������ �� �������� � ��������� ������ �� ����� sotmarket.ru � ����������� REF ������. <br/>
                {title} - �������� ������ <br/>
                {image_src} - ���� � ����������� ������ <br/>
                {price} - ���� ������ <br/>
                {old_price} - ���� �� ���������� <br/>
                {sale} - ����� SALE, � ���� ���������� ������ ���� �� ������ ���� ����������<br/>


				</p>
			</div>
			<div>
				<a href="$sSotmarketPluginUrl&template=$sTemplateName&new=1">������� ����� ������ �� ������ �����</a>
			</div>
END;
    echofooter();
    exit;
}

//��������� ������� ������� �� ��������� �������
if ($_POST['hidden_send_template'] == 'Y') {

    if ( isset( $_POST['new_template_name'] ) ){
        $sTemplateName = $_POST['new_template_name'];
    } else {
        $sTemplateName = $_POST['hidden_template_name'];
    }

    $sTemplateContent = $_POST['template-code'];
    //����� �� wp ���������� �������
    $sTemplateContent = str_replace('\"','"',$sTemplateContent);
    $sTemplateContent = str_replace("\'","'",$sTemplateContent);

    file_put_contents($sSotmarketTemplatesPath . $sTemplateName . '.tpl',$sTemplateContent);
    echo '<div class="updated"><p><strong>������ ��������</strong></p></div>';
}



$db->query("SELECT * FROM `" . PREFIX . "_sotmarket_settings`");

$aSettings = array();
while ($row = $db->get_row()) {
    $aSettings[$row['sm_key']] = $row['sm_value'];
}

?>
<script src="engine/skins/tabset.js" type="text/javascript"></script>
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
        <a content="cont_1">���������</a>
        <a content="cont_2">�������</a>
        <a  content="cont_3">������������ � ������</a>
    </div>
    <div>
        <div id="cont_1">
            <strong>IP �����:</strong> <?= $sServerIp ?>
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
            <strong>��������� ������� ������ sotmarket:</strong><br /><br />
            <?

            foreach( $aTemplateFiles as $sTemplate ){
                echo ' - <a href="' . $sSotmarketPluginUrl. '&template=' . $sTemplate . '">' . $sTemplate . '</a><br/>';
            }
            ?>
        </div>
        <div id="cont_3">
            ������������ � ����������, �������� ������.<br /><br />
            <strong class='bbc'>�������� ����������:</strong><br />
            - <a href='http://www.partner.sotmarket.ru/forum/index.php?showtopic=3275' class='bbc_url' title=''>������� ���������� � ������� � ����������� ��������</a>
            - <a href='http://www.partner.sotmarket.ru/forum/index.php?showtopic=3277' class='bbc_url' title=''>������������� �������� ��� ������ ����������</a><br />
            - <a href='http://www.partner.sotmarket.ru/forum/index.php?showtopic=3279' class='bbc_url' title=''>��������� ����� ��� ������� CMS DLE</a><br />
            - <a href='http://www.partner.sotmarket.ru/forum/index.php?showtopic=3273' class='bbc_url' title=''>������� ���������� � ������� �����</a><br />
            - <a href='http://www.partner.sotmarket.ru/forum/index.php?showtopic=3271' class='bbc_url' title=''>����� ������ ����� ������� �� �����</a><br />
            <br /><br />
            <form method = "POST">
                <input type = "submit" name = "uninstall" value = "������� ������" class="btn btn-success" />
            </form>
        </div>
    </div>
</div>
<?php

echofooter();
