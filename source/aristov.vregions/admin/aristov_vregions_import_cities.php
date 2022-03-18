<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $USER, $APPLICATION, $DB;
$moduleID = 'aristov.vregions';
CModule::IncludeModule('aristov.vregions');

$APPLICATION->SetTitle(Loc::getMessage("TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); ?>
<?php
if ($_REQUEST['import']){

    $iblockID = \Aristov\VRegions\Tools::getModuleOption("vregions_iblock_id");

    if ($iblockID){

        require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/aristov.vregions/lib/listOfCities.php");

        foreach ($cities as $city){
            if (!$city['NAME'] || !$city['CODE']){
                continue;
            }

            $elArr = Array(
                'IBLOCK_ID'       => $iblockID,
                'NAME'            => $city['NAME'],
                'CODE'            => $city['CODE'],
                'PROPERTY_VALUES' => Array(
                    'CENTR_REGIONA' => $city['LAT'].','.$city['LON'],
                    'WHERE'         => $city['PREDL_PAD'],
                ),
                "ACTIVE"          => "Y"
            );

            $res = CIBlockElement::GetList(
                Array(
                    "SORT" => "ASC"
                ),
                Array(
                    'IBLOCK_ID' => $elArr['IBLOCK_ID'],
                    'CODE'      => $elArr['CODE'],
                ),
                false,
                false,
                Array(
                    'ID',
                    'NAME',
                    'IBLOCK_ID',
                    'PROPERTY_WHERE',
                )
            );
            if ($existedFields = $res->GetNext(true, false)){
                if (!$existedFields['PROPERTY_WHERE_VALUE']){
                    CIBlockElement::SetPropertyValuesEx($existedFields['ID'], $existedFields['IBLOCK_ID'], array('WHERE' => $city['PREDL_PAD']));
                    echo CAdminMessage::ShowNote(Loc::getMessage("UPDATED_CITY").' "'.$existedFields['NAME'].'"');
                }
            } else{
                $el = new CIBlockElement;
                if ($PRODUCT_ID = $el->Add($elArr)){
                    echo CAdminMessage::ShowNote(Loc::getMessage("IMPORTED_CITY").' "'.$elArr['NAME'].'"');
                }
            }
        }
    }
}
?>
<form>
	<button name="import"
	        value="y"><?=Loc::getMessage("IMPORT");?></button>
</form>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>
