<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $USER, $APPLICATION, $DB;
$moduleID = 'aristov.vregions';
CModule::IncludeModule('aristov.vregions');

$APPLICATION->SetTitle(Loc::getMessage("TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); ?>
<?php

echo '<h2>VREGIONS_REGION</h2>';
echo "<pre>";
print_r($_SESSION['VREGIONS_REGION']);
echo "</pre>";
echo '<h2>VREGIONS_DEFAULT</h2>';
echo "<pre>";
print_r($_SESSION['VREGIONS_DEFAULT']);
echo "</pre>";
echo '<h2>VREGIONS_PHP</h2>';
echo "<pre>";
print_r($_SESSION['VREGIONS_PHP']);
echo "</pre>";
echo '<h2>VREGIONS_IM_LOCATION</h2>';
echo "<pre>";
print_r($_SESSION['VREGIONS_IM_LOCATION']);
echo "</pre>";
echo '<h2>VREGIONS_DEBUG</h2>';
echo "<pre>";
print_r($_SESSION['VREGIONS_DEBUG']);
echo "</pre>";
echo '<h2>$_SERVER</h2>';
echo "<pre>";
print_r($_SERVER);
echo "</pre>";

?>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>