<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

CModule::IncludeModule('sale');
$deliveries     = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();
$deliveriesList = Array(GetMessage("ARISTOV_DELIVERY.DONT_EXCLUDE"));
foreach ($deliveries as $delivery){
    $deliveriesList[$delivery['ID']] = $delivery['NAME'];
}

$arComponentParameters = array(
    "GROUPS"     => array(),
    "PARAMETERS" => array(
        "ID_TOVARA"                     => Array(
            "PARENT" => "BASE",
            "NAME"   => GetMessage("ARISTOV_DELIVERY.CALC_PARAM_ID_TOVARA_NAME"),
            "TYPE"   => "STRING",
        ),
        "LOCATION_CODE"                 => Array(
            "PARENT" => "BASE",
            "NAME"   => GetMessage("ARISTOV_DELIVERY.CALC_PARAM_LOCATION_CODE_NAME"),
            "TYPE"   => "STRING",
        ),
        "PERSON_TYPE_ID"                => Array(
            "PARENT" => "BASE",
            "NAME"   => GetMessage("ARISTOV_DELIVERY.CALC_PARAM_PERSON_TYPE_ID_NAME"),
            "TYPE"   => "STRING",
        ),
        "TITLE"                         => Array(
            "PARENT"  => "BASE",
            "NAME"    => GetMessage("ARISTOV_DELIVERY.CALC_PARAM_TITLE_NAME"),
            "DEFAULT" => GetMessage("ARISTOV_DELIVERY.CALC_PARAM_TITLE_DEFAULT"),
            "TYPE"    => "STRING",
        ),
        "DONT_INCLUDE_PRODUCT_IN_CACHE" => Array(
            "PARENT"  => "BASE",
            "NAME"    => GetMessage("ARISTOV_DELIVERY.CALC_PARAM_DONT_INCLUDE_PRODUCT_IN_CACHE_DEFAULT"),
            "TYPE"    => "CHECKBOX",
            "DEFAULT" => "N",
        ),
        "EXCLUDE_DELIVERIES"            => Array(
            "PARENT"   => "BASE",
            "NAME"     => GetMessage("ARISTOV_DELIVERY.CALC_PARAM_EXCLUDE_DELIVERIES_NAME"),
            "TYPE"     => "LIST",
            "DEFAULT"  => "ASC",
            "VALUES"   => $deliveriesList,
            "MULTIPLE" => 'Y'
        ),
        "CACHE_TIME"                    => Array("DEFAULT" => 3600),
    ),
);
?>
