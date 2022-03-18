<?

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

CModule::IncludeModule('aristov.vregions');

if (class_exists('AristovVregionsHelper')){
    if (!\AristovVregionsHelper::isDemoEnd()){
        AddEventHandler("main", "OnBuildGlobalMenu", "global_menu_aristov_vregions");
    }
}

function global_menu_aristov_vregions(&$aGlobalMenu, &$aModuleMenu){
    $aModuleMenu[] = Array(
        "parent_menu" => "global_menu_services",
        "icon"        => "default_menu_icon",
        "page_icon"   => "default_page_icon",
        "text"        => Loc::getMessage("ARISTOV_VREGIONS_ADMIN_MENU_REGIONY_PRODAG_TEXT"),
        "title"       => Loc::getMessage("ARISTOV_VREGIONS_ADMIN_MENU_REGIONY_PRODAG_TITLE"),
        "url"         => "aristov_vregions_service_body.php",
        "items"       => Array(
            array(
                "text"  => Loc::getMessage("ARISTOV_VREGIONS_SERVICE_GEOLOCATION_TEST_TEXT"),
                "title" => Loc::getMessage("ARISTOV_VREGIONS_SERVICE_GEOLOCATION_TEST_TITLE"),
                "url"   => "aristov_vregions_service_geolocation_test.php",
            ),
            array(
                "text"  => Loc::getMessage("ARISTOV_VREGIONS_SERVICE_HANDLERS_TEST_TEXT"),
                "title" => Loc::getMessage("ARISTOV_VREGIONS_SERVICE_HANDLERS_TEST_TITLE"),
                "url"   => "aristov_vregions_service_handlers_test.php",
            ),
            array(
                "text"  => Loc::getMessage("ARISTOV_VREGIONS_DEBUG_TEXT"),
                "title" => Loc::getMessage("ARISTOV_VREGIONS_DEBUG_TITLE"),
                "url"   => "aristov_vregions_debug.php",
            ),
            array(
                "text"  => Loc::getMessage("ARISTOV_VREGIONS_IMPORT_TEXT"),
                "title" => Loc::getMessage("ARISTOV_VREGIONS_IMPORT_TITLE"),
                "url"   => "aristov_vregions_import_cities.php",
            ),
            array(
                "text"  => Loc::getMessage("ARISTOV_VREGIONS_REGIONAL_PRICES_TEXT"),
                "title" => Loc::getMessage("ARISTOV_VREGIONS_REGIONAL_PRICES_TITLE"),
                "url"   => "aristov_vregions_regional_prices_cities.php",
            ),
            array(
                "text"  => Loc::getMessage("ARISTOV_VREGIONS_YML_TEXT"),
                "title" => Loc::getMessage("ARISTOV_VREGIONS_YML_TITLE"),
                "url"   => "aristov_vregions_yml.php",
            )
        )
    );
}