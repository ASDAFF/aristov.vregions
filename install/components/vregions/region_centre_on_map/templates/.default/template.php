<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
mb_internal_encoding("cp-1251");
$textStrlen = strlen($arParams["BALLOON_TEXT"]);
mb_internal_encoding(LANG_CHARSET);

if (is_array($arParams["BALLOON_TEXT"]) && isset($arParams["BALLOON_TEXT"]['TEXT'])){
    $arParams["BALLOON_TEXT"] = $arParams["BALLOON_TEXT"]['TEXT'];
}

$mapData = serialize(
    Array(
        'yandex_lat'   => (float) $arResult["COORDS"][0],
        'yandex_lon'   => (float) $arResult["COORDS"][1],
        'yandex_scale' => (integer) ($arParams["ZOOM"] ?: 11),
        'PLACEMARKS'   => Array(
            Array(
                'LAT'  => (float) $arResult["COORDS"][0],
                'LON'  => (float) $arResult["COORDS"][1],
                'TEXT' => $arParams["BALLOON_TEXT"],
            ),
        ),
    )
);
?>
<? $APPLICATION->IncludeComponent(
    "bitrix:map.yandex.view",
    "",
    Array(
        "CONTROLS"      => $arParams['CONTROLS'],
        "INIT_MAP_TYPE" => "MAP",
        "MAP_DATA"      => $mapData,
        "MAP_HEIGHT"    => $arParams["HEIGHT"] ?: 500,
        "MAP_ID"        => "",
        "MAP_WIDTH"     => $arParams["WIDTH"] ?: 600,
        "OPTIONS"       => $arParams['OPTIONS']
    ),
    $component
); ?>