<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

if (!CModule::IncludeModule("iblock")){
    return;
}

$arSorts = Array(
    "ASC"  => GetMessage("T_IBLOCK_DESC_ASC"),
    "DESC" => GetMessage("T_IBLOCK_DESC_DESC"),
);

$arSortFields = Array(
    "ID"          => GetMessage("T_IBLOCK_DESC_FID"),
    "NAME"        => GetMessage("T_IBLOCK_DESC_FNAME"),
    "ACTIVE_FROM" => GetMessage("T_IBLOCK_DESC_FACT"),
    "SORT"        => GetMessage("T_IBLOCK_DESC_FSORT"),
    "TIMESTAMP_X" => GetMessage("T_IBLOCK_DESC_FTSAMP")
);

$arComponentParameters = array(
    "GROUPS"     => array(),
    "PARAMETERS" => array(
        "SORT_BY1"                       => Array(
            "PARENT"            => "DATA_SOURCE",
            "NAME"              => GetMessage("T_IBLOCK_DESC_IBORD1"),
            "TYPE"              => "LIST",
            "DEFAULT"           => "NAME",
            "VALUES"            => $arSortFields,
            "ADDITIONAL_VALUES" => "Y",
        ),
        "SORT_ORDER1"                    => Array(
            "PARENT"  => "DATA_SOURCE",
            "NAME"    => GetMessage("T_IBLOCK_DESC_IBBY1"),
            "TYPE"    => "LIST",
            "DEFAULT" => "ASC",
            "VALUES"  => $arSorts,
        ),
        "SORT_BY2"                       => Array(
            "PARENT"            => "DATA_SOURCE",
            "NAME"              => GetMessage("T_IBLOCK_DESC_IBORD2"),
            "TYPE"              => "LIST",
            "DEFAULT"           => "SORT",
            "VALUES"            => $arSortFields,
            "ADDITIONAL_VALUES" => "Y",
        ),
        "SORT_ORDER2"                    => Array(
            "PARENT"  => "DATA_SOURCE",
            "NAME"    => GetMessage("T_IBLOCK_DESC_IBBY2"),
            "TYPE"    => "LIST",
            "DEFAULT" => "ASC",
            "VALUES"  => $arSorts,
        ),
        "INCLUDE_PATH_IN_LINKS" => Array(
            "PARENT"  => "ADDITIONAL_SETTINGS",
            "NAME"    => GetMessage("PARAM_INCLUDE_PATH_IN_LINKS_TITLE"),
            "TYPE"    => "CHECKBOX",
            "DEFAULT" => "N",
        ),
        "INCLUDE_PROPS_ARRAY"            => Array(
            "PARENT"  => "DATA_SOURCE",
            "NAME"    => GetMessage("PARAM_INCLUDE_PROPS_ARRAY_TITLE"),
            "TYPE"    => "CHECKBOX",
            "DEFAULT" => "N",
        ),
        "CACHE_TIME"                     => Array("DEFAULT" => 3600),
    ),
);
?>