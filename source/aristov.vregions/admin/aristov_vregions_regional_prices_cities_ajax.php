<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $USER, $APPLICATION, $DB;
$moduleID = 'aristov.vregions';
CModule::IncludeModule('aristov.vregions');

CModule::IncludeModule('iblock');

$fromPriceID = $_REQUEST['fromPriceID'];
$multiplier = trim($_REQUEST['multiplier']);
$toPriceID = $_REQUEST['toPriceID'];
$toPriceName = iconv('utf-8', LANG_CHARSET, $_REQUEST['toPriceName']);
$productIDs = explode(',', $_REQUEST['productIds']);
$handledProducts = Aristov\VRegions\Tools::setProductsPriceByAnotherPrice($fromPriceID, $toPriceID, $multiplier, Array(
	'ID' => $productIDs,
));

foreach ($handledProducts as $handledProduct){
	echo CAdminMessage::ShowNote(Loc::getMessage("FOR_PRODUCT").' "'.$handledProduct['NAME'].'" '.Loc::getMessage("IS_SET_PRICE").' "'.$toPriceName.'" '.Loc::getMessage("IN_VALUE").' '.$handledProduct['NEW_PRICE_VALUE']);
}