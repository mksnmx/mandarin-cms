<?php
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

IncludeModuleLangFile(__FILE__);

Class mandarinbank_mandarinbank extends CModule
{
	const MODULE_ID = 'mandarinbank.mandarinbank';
	var $MODULE_ID = 'mandarinbank.mandarinbank';
	var $MODULE_VERSION = '1.0.1';
	var $MODULE_VERSION_DATE = '2017-01-27';
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;

	var $strError = '';

	function __construct() {
		$this->PARTNER_NAME = "ннн ояо";
		$this->PARTNER_URI = "https://mandarinbank.com";
		$this->MODULE_NAME = GetMessage("MANDARIN_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("MANDARIN_MODULE_DESC");   
	}

	function InstallEvents() {
		return true;
	}

	function UnInstallEvents() {
		return true;
	}

	function rmFolder($dir) {
		foreach(glob($dir . '/*') as $file) {
			if(is_dir($file)){
				$this->rmFolder($file);
			} else {
				unlink($file);
			}
		}
		rmdir($dir);

		return true;
	}

	function copyDir( $source, $destination ) {
		if ( is_dir( $source ) ) {
			@mkdir( $destination, 0755 );
			$directory = dir( $source );
			while ( FALSE !== ( $readdirectory = $directory->read() ) ) {
				if ( $readdirectory == '.' || $readdirectory == '..' ) continue;
				$PathDir = $source . '/' . $readdirectory; 
				if ( is_dir( $PathDir ) ) {
					$this->copyDir( $PathDir, $destination . '/' . $readdirectory );
					continue;
				}
			copy( $PathDir, $destination . '/' . $readdirectory );
			}
			$directory->close();
		} else {
			copy( $source, $destination );
		}
	}

	function InstallFiles($arParams = array()) {
		if ( !is_dir($dir_spm = $_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/sale_payment/') ) {
			mkdir($dir_spm, 0755);
		}

		if ( !is_dir($dir_pay = $_SERVER['DOCUMENT_ROOT'].'/payment/') ) {
			mkdir($dir_pay, 0755);
		}

		if (is_dir($source = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install')) {
			$this->copyDir("$source/sale_payment", $dir_spm);
			$this->copyDir("$source/payment", $dir_pay);
		}
		return true;
	}

	function UnInstallFiles() {
		$this->rmFolder($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/sale_payment/mandarinbank');
		$this->rmFolder($_SERVER['DOCUMENT_ROOT'].'/payment/mandarinbank');
		return true;
	}

	function DoInstall() {
		$this->InstallFiles();
		ModuleManager::registerModule('mandarinbank.mandarinbank');
	}

	function DoUninstall() {
		ModuleManager::unRegisterModule('mandarinbank.mandarinbank');
		$this->UnInstallFiles();
	}
}
?>
