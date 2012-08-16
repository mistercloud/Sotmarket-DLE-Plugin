<?php
include_once(ENGINE_DIR . '/modules/sotmarket/lang/' . strtolower($config['langs']) . '.php');

echoheader('Sotmarket Module Install', $aTranslations['INSTALL_MODULE']);


$sAction = 'none';

function bSaveXFieldsFile($aLines)
{
    $filehandle = fopen(ENGINE_DIR . '/data/xfields.txt', "w+");
    if (!$filehandle) return false;
    foreach ($aLines as $sLine) {
        if (empty($sLine)) continue;
        fputs($filehandle, $sLine);
    }
    fclose($filehandle);
    return true;
}

function aGetXFieldsLines()
{
    $filehandle = fopen(ENGINE_DIR . '/data/xfields.txt', "r");
    if (!$filehandle) return false;
    $aResult = array();
    while ($sLine = fgets($filehandle, 1024)) {
        if (empty($sLine)) continue;
        $aResult[] = $sLine;
    }
    fclose($filehandle);
    return $aResult;
}

function vInstallTemplates()
{
    $aDirectories = array();
    $aSmTemplates = array();
    foreach (glob("templates/*") as $filename) {
        if (is_dir($filename)) {
            $aDirectories[] = $filename;
        } else {
            if (strpos($filename, 'sotmarket') !== false)
                $aSmTemplates[] = $filename;
        }
    }
    foreach ($aDirectories as $sDirectory) {
        foreach ($aSmTemplates as $sFileTemplate) {
            $sNewFileName = $sDirectory . strrchr($sFileTemplate, '/');
            copy($sFileTemplate, $sNewFileName);
        }
    }
}

function vUninstallTemplates()
{
    $aDirectories = array();
    $aSmTemplates = array();
    foreach (glob("templates/*") as $filename) {
        if (is_dir($filename)) {
            $aDirectories[] = $filename;
        } else {
            if (strpos($filename, 'sotmarket') !== false) {
                $aSmTemplates[] = $filename;
                @unlink($filename);
            }
        }
    }
    foreach ($aDirectories as $sDirectory) {
        foreach ($aSmTemplates as $sFileTemplate) {
            $sNewFileName = $sDirectory . strrchr($sFileTemplate, '/');
            @unlink($sNewFileName);
        }
    }
}

if (isset($_POST["install"])) $sAction = 'install';
if (isset($_POST["uninstall"])) $sAction = 'uninstall';
if (isset($_POST["uninstall2"])) $sAction = 'uninstall_confirm';

switch ($sAction) {
    case 'install':
        $dle_api->install_admin_module('sotmarket', $aTranslations['MODULE_TITLE'], $aTranslations['MODULE_DESCRIPTION'], 'logo-sotmarket.png', 'admin');

        $db->query("
			CREATE TABLE IF NOT EXISTS `" . PREFIX . "_sotmarket_settings` (
				`sm_key` char(32),
				`sm_value` char(32),
				UNIQUE KEY `sm_key_uniq_idx` (`sm_key`)
			) ENGINE=MyISAM"
        );

        $axLine = aGetXFieldsLines();
        $aResult = array();
        foreach ($axLine as $sLine) {
            if (strpos($sLine, 'sotmarket_product') === false) {
                $aResult[] = $sLine."\r\n";
            }
        }
        $aResult[] = "sotmarket_product_id|ID товара на сайте Sotmarket||text||1|0\r\n";
        $aResult[] = "sotmarket_product_name|Название товара для поиска товаров Sotmarket||text||1|0\r\n";
        bSaveXFieldsFile($aResult);

        echo $aTranslations['INSTALL_MODULE_SUCCESS'];

        /*if ($ok) {

        } else {
            echo<<<END
            <div style="text-align:center; width:98%;">
                <h3>$aTranslations[INSTALL_MODULE_UNSUCCESS]</h3>
            </div>
END;

        }*/
        break;
    case 'uninstall':



        echo<<<END
		<div style="text-align:center; width:98%;">
		<h3>$aTranslations[UNINSTALL_MODULE]</h3><br />
        <form method = "POST">
			<input type = "hidden" name = "uninstall" value = "1" />
			<input type = "submit" name = "uninstall2" value = "$aTranslations[UNINSTALL_MODULE]" />
		</form>
        <br /><a href="$config[http_home_url]/admin.php?mod=sotmarket">$aTranslations[UNINSTALL_MODULE_SKIP]</a>
		</div>
END;
        break;
    case 'uninstall_confirm':

        $axLine = aGetXFieldsLines();
        $aResult = array();
        foreach ($axLine as $sLine) {
            if (strpos($sLine, 'sotmarket_product') === false) {
                $aResult[] = $sLine;
            }
        }
        bSaveXFieldsFile($aResult);
        //vUninstallTemplates();

        $db->query("DROP TABLE IF  EXISTS `" . PREFIX . "_sotmarket_settings`");
        $dle_api->uninstall_admin_module('sotmarket');
        echo<<<END
        <div style="text-align:center; width:98%;">
            <h3>$aTranslations[UNINSTALL_MODULE_SUCCESS]</h3>
        </div>
END;
        break;
    default:
    	echo <<<END
		<div style="text-align:center; width:98%;">
		<h3>$aTranslations[INSTALL_MODULE]</h3><br />
		<form method = "POST">
			<input type = "submit" name = "install" value = "$aTranslations[INSTALL_MODULE]" />
		</form>
		</div>
END;

}
echofooter();
?>