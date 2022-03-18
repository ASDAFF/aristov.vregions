<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

CJSCore::Init(array('ajax')); // zashhita ot govnoshablona
\Bitrix\Main\Page\Asset::getInstance()->addJs($this->GetPath()."/script.js");

$arParams["SORT_BY1"]    = trim($arParams["SORT_BY1"]) ?: (\Aristov\VRegions\Tools::getModuleOption("header_select_sort_by1") ?: 'SORT');
$arParams["SORT_BY2"]    = trim($arParams["SORT_BY2"]) ?: (\Aristov\VRegions\Tools::getModuleOption("header_select_sort_by2") ?: 'NAME');
$arParams["SORT_ORDER1"] = trim($arParams["SORT_ORDER1"]) ?: (\Aristov\VRegions\Tools::getModuleOption("header_select_sort_order1") ?: 'DESC');
$arParams["SORT_ORDER2"] = trim($arParams["SORT_ORDER2"]) ?: (\Aristov\VRegions\Tools::getModuleOption("header_select_sort_order2") ?: 'ASC');
$arParams["CACHE_TIME"]  = intval($arParams["CACHE_TIME"] ?: (\Aristov\VRegions\Tools::getModuleOption("header_select_cache_time") ?: 3600));

$arResult                          = array();
$arResult["CURRENT_SESSION_ARRAY"] = $_SESSION["VREGIONS_REGION"];

CModule::IncludeModule('iblock');
CModule::IncludeModule('aristov.vregions');

if (!class_exists('AristovVregionsHelper')){
    return;
}

if (!\AristovVregionsHelper::isDemoEnd()){
    $arResult["ITEMS"] = AristovVregionsComponent::getRegionsList(
        $arParams["SORT_BY1"],
        $arParams["SORT_ORDER1"],
        $arParams["SORT_BY2"],
        $arParams["SORT_ORDER2"],
        $arParams['INCLUDE_PROPS_ARRAY'] == 'Y',
        $arParams["CACHE_TIME"]
    );

    foreach ($arResult['ITEMS'] as &$item){
        if ($item["~CODE"] == $arResult["CURRENT_SESSION_ARRAY"]["CODE"]){
            $item["ACTIVE"] = true;
        }

        if ($arParams["INCLUDE_PATH_IN_LINKS"] == 'Y'){
            $item["PATH"] = AristovVregionsComponent::getCurrentPath();
        }
    }

    $this->IncludeComponentTemplate();
}
