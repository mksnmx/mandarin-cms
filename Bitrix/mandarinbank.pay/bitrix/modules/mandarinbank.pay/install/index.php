<?php
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

IncludeModuleLangFile(__FILE__);

Class mandarinbank_pay extends CModule
{
    var $MODULE_ID = 'mandarinbank.pay';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    var $strError = '';

    function __construct(){
        $this->PARTNER_NAME = 'vuchastyi';
        $this->PARTNER_URI = 'https://github.com/vuchastyi';

        include(dirname(__FILE__) . "/version.php");

        if(is_array($arModuleVersion) && array_key_exists('VERSION',$arModuleVersion)){
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = GetMessage('MANDARIN_MODULE_NAME_HOSTED');
        $this->MODULE_DESCRIPTION = GetMessage('MANDARIN_MODULE_DESC_HOSTED');
    }

    function InstallEvents(){
        return true;
    }
    function UnInstallEvents(){
        return true;
    }

    function rmFolder($dir){
        if(is_dir($dir)){
            $objects = scandir($dir);
            foreach($objects as $object){
                if($object !== '.' && $object !== '..'){
                    if(filetype($dir.'/'.$object) === 'dir')$this->rmFolder($dir.'/'.$object);else unlink($dir.'/'.$object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    function copyDir($source,$destination){
        if(is_dir($source)){
            @mkdir($destination,0755);
            $directory = dir($source);
            while(FALSE !== ($readdirectory = $directory->read())){
                if($readdirectory === '.' || $readdirectory === '..')continue;

                $PathDir = $source . '/' . $readdirectory;
                if(is_dir($PathDir)){
                    $this->copyDir($PathDir,$destination.'/'.$readdirectory);
                    continue;
                }
                copy($PathDir,$destination.'/'.$readdirectory);
            }
            $directory->close();
        }else copy($source,$destination);
    }

    function InstallFiles($arParams = array()) {
        if (!is_dir($dir_spm = $_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/sale_payment/')){
            mkdir($dir_spm,0755);
        }

        if (!is_dir($dir_pay = $_SERVER['DOCUMENT_ROOT'].'/payment/')){
            mkdir($dir_pay,0755);
        }

        if(is_dir($source = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install')) {
            $this->copyDir("$source/sale_payment", $dir_spm);
            $this->copyDir("$source/payment", $dir_pay);
        }
        return true;
    }

    function UnInstallFiles() {
        $this->rmFolder($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/sale_payment/mandarinbank_pay');
        $this->rmFolder($_SERVER['DOCUMENT_ROOT'].'/payment/mandarinbank_pay');
        return true;
    }
    function DoInstall() {
        $this->InstallFiles();
        ModuleManager::registerModule($this->MODULE_ID);
    }
    function DoUninstall() {
        ModuleManager::unRegisterModule($this->MODULE_ID);
        $this->UnInstallFiles();
    }
}
