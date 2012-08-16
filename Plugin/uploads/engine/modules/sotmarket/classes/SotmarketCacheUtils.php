<?php
set_time_limit(0);
/***
 *  Пример использования


$cashe_dir='/home/ВАШ ПУТЬ/cacheuser/title/EmptyMe';
$mods_dir='/home/ВАШ ПУТЬ/mods/EmptyMe';

$oUtils = new cCacheUtilites();
$oUtils->vAddDir($cashe_dir);
$oUtils->vAddDir($mods_dir);
$oUtils->vProcess();
if ($oUtils->bNoErrors()){
    echo "Удаление прошло успешно. Удалено : Файлов ".$oUtils->sGetStat('files_ok').", Директорий : ".$oUtils->sGetStat('dirs_ok')."<br/>\r\n";
    echo "Не удалено: Файлов ".$oUtils->sGetStat('files_failed').", Директорий : ".$oUtils->sGetStat('dirs_failed');
}else{
    foreach($oUtils->aGetErrors() as $sError){
        echo "Ошибка: ".$sError."<br/>\r\n";
    }
}
 **/
class cCacheUtilites {
    protected $aDirs = array();
    protected $aErrors = array();
    protected $aStat = array();

    /**
     * Пустой конструктор
     */
    public function __construct() {

    }

    /**
     * @param $sDirName добавляемый путь к директории
     */
    public function vAddDir($sDirName) {
        if (!in_array($sDirName, $this->aDirs)) {
            $this->aDirs[] = $sDirName;
        }
    }

    /**
     * Запускает удаление добавленых директорий
     */
    public function vProcess() {
        $this->aErorrs = array();
        $this->aStat   = array('files_ok'    => 0,
                               'files_failed'=> 0,
                               'dirs_ok'     => 0,
                               'dirs_failed' => 0);
        foreach ($this->aDirs as $sDirectory) {
            try {
                $this->vClearDir($sDirectory);
            } catch (Exception $e) {
                $this->aErorrs[] = $e->getMessage();
            }
        }
    }

    /**
     * Рекурсивная функция удаления файлов и директорий из текущей директории
     * @param $sDir директория из который удаляются файлы
     * @throws Exception если переданная директория не доступна или не директория
     */
    public function vClearDir($sDir) {
        if (!is_dir($sDir)) {
            throw new Exception('suggested path ' . $sDir . ' is not actually a dir');
        }
        foreach (glob($sDir . '/*') as $sElementPath) {
            if (is_dir($sElementPath)) {
                $this->vClearDir($sElementPath);
                if (@rmdir($sElementPath)) {
                    $this->aStat['dirs_ok']++;
                } else {
                    $this->aStat['dirs_failed']++;
                }
            } else {
                if (!is_writable($sElementPath)) {
                    chmod($sElementPath, 0666);
                }
                if (@unlink($sElementPath)) {
                    $this->aStat['files_ok']++;
                } else {
                    $this->aStat['files_failed']++;
                }
            }
        }
    }

    /**
     * @return bool Возвращает true если ошибок не было
     */
    public function bNoErrors() {
        return empty($this->aErrors);
    }

    /**
     * @return array возвращает ошибки
     */
    public function aGetErrors() {
        return $this->aErrors;
    }

    /**
     * @param string $type варианты files_ok, files_failed, dirs_ok, dirs_failed
     * @return string данные статистики
     **/
    public function sGetStat($type) {
        return isset($this->aStat[$type]) ? $this->aStat[$type] : 'not found';
    }
}
