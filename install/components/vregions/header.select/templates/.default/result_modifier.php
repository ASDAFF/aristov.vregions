<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

$arParams['ALLOW_OBLAST_FILTER']       = $arParams["ALLOW_OBLAST_FILTER"] ?: (\Aristov\VRegions\Tools::getModuleOption("header_select_allow_oblast_filter") ?: 'N');
$arParams["COLS_COUNT"]                = $arParams["COLS_COUNT"] ?: (\Aristov\VRegions\Tools::getModuleOption("header_select_cols_count") ?: 3);
$arParams["POPUP_QUESTION_TITLE"]      = $arParams["POPUP_QUESTION_TITLE"] ?: \Aristov\VRegions\Tools::getModuleOption("header_select_popup_question_title");
$arParams["SHOW_SEARCH_FORM"]          = $arParams["SHOW_SEARCH_FORM"] ?: (\Aristov\VRegions\Tools::getModuleOption("header_select_show_search_form") ?: 'N');
$arParams["ALLOW_OBLAST_FILTER"]       = $arParams["ALLOW_OBLAST_FILTER"] ?: (\Aristov\VRegions\Tools::getModuleOption("header_select_allow_oblast_filter") ?: 'N');
$arParams["FIXED"]                     = $arParams["FIXED"] ?: (\Aristov\VRegions\Tools::getModuleOption("header_select_fixed") ?: 'N');
$arParams["STRING_BEFORE_REGION_LINK"] = $arParams["STRING_BEFORE_REGION_LINK"] ?: \Aristov\VRegions\Tools::getModuleOption("header_select_string_before_region_link");
$arParams["SHOW_POPUP_QUESTION"]       = $arParams["SHOW_POPUP_QUESTION"] ?: \Aristov\VRegions\Tools::getModuleOption("header_select_show_popup_question" ?: 'N');
$arParams["SHOW_ANOTHER_REGION_BTN"]   = $arParams["SHOW_ANOTHER_REGION_BTN"] ?: \Aristov\VRegions\Tools::getModuleOption("header_select_show_another_region_btn" ?: 'N');

// izbrannye regiony и области
$arResult["CHOSEN_ITEMS"] = Array();
$arResult["OBLASTI"]      = Array();
foreach ($arResult["ITEMS"] as $item){
    if (
        $item["CHOSEN_ONE"] == "Y" ||
        $item["CHOSEN_ONE"] == "y"
    ){
        $arResult["CHOSEN_ITEMS"][] = $item;
    }

    if ($arParams['ALLOW_OBLAST_FILTER'] == 'Y'){
        // собрать все области
        if ($item["OBLAST"]){
            $arResult["OBLASTI"][] = $item["OBLAST"];
        }
    }
}
if ($arResult["OBLASTI"]){
    $arResult["OBLASTI"] = array_unique($arResult["OBLASTI"]);
    sort($arResult["OBLASTI"]);
}

// razdelenie na kolonki
$colsCount    = $arParams["COLS_COUNT"];
$maxColsCount = 6;
if ($colsCount > $maxColsCount){
    $colsCount = $maxColsCount;
}
$arResult["COUNT_SECTION"] = count($arResult["ITEMS"]) / $colsCount;
$arResult["COLS"]          = array_chunk($arResult["ITEMS"], ceil($arResult["COUNT_SECTION"]));
// показ областей слева съедает одну колонку
if ($arParams['SHOW_OBLAST_LEFT'] == 'Y'){
    $colsCount++;
}
$arResult["COL_CLASS"]          = 'vregions-list__col_width-one-'.$colsCount;
$arResult["CHOSEN_ITEMS_CLASS"] = 'vregions-chosen-list__item-one-'.$colsCount;