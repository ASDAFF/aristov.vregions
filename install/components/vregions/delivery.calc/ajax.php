<? require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
IncludeModuleLangFile(__FILE__);

if (!CModule::IncludeModule("iblock")){
    return;
}
if (!CModule::IncludeModule("aristov.vregions")){
    return;
}
if (\AristovVregionsHelper::isDemoEnd()){
    return;
}

global $USER, $APPLICATION;

$answer            = Array();
$answer["success"] = false;
$action            = $_REQUEST["action"];

switch($action){
    case 'get-location-by-mask':
        $locations = Array();
        $ids       = Array(); // chtoby ubrat' dubljazh

        if (\CModule::IncludeModule("sale")){
            // snazhala sopostovlyaem po imeni regiona
            $db_vars = \CSaleLocation::GetList(
                array(
                    "SORT" => "ASC",
                ),
                array(
                    '%CITY_NAME_LANG' => htmlspecialcharsbx($_REQUEST['mask'])
                ),
                false,
                false,
                array(
                    'ID',
                    'COUNTRY_ID',
                    'REGION_ID',
                    'CITY_ID',
                    'SORT',
                    'COUNTRY_NAME_ORIG',
                    'COUNTRY_SHORT_NAME',
                    'REGION_NAME_ORIG',
                    'CITY_NAME_ORIG',
                    'REGION_SHORT_NAME',
                    'CITY_SHORT_NAME',
                    'COUNTRY_LID',
                    'COUNTRY_NAME',
                    'REGION_LID',
                    'CITY_LID',
                    'REGION_NAME',
                    'CITY_NAME',
                    'LOC_DEFAULT',
                    'CODE'
                )
            );
            if ($db_vars->SelectedRowsCount()){
                while($location = $db_vars->Fetch()){
                    if (!in_array($location["ID"], $ids)){

                        if (LANG_CHARSET != 'UTF-8'){
                            $location["CITY_NAME"] = iconv(LANG_CHARSET, 'UTF-8', $location["CITY_NAME"]);
                        }
                        if (LANG_CHARSET != 'UTF-8'){
                            $location["COUNTRY_NAME"] = iconv(LANG_CHARSET, 'UTF-8', $location["COUNTRY_NAME"]);
                        }

                        $locations[] = $location;
                        $ids[]       = $location["ID"];
                    }
                }
            }
        }

        $answer['locations'] = $locations;

        break;

    case 'get-delivery-component-for-location':
        $APPLICATION->IncludeComponent(
            "vregions:delivery.calc",
            "",
            Array(
                "CACHE_TIME"                    => "3600",
                "CACHE_TYPE"                    => "A",
                "COMPOSITE_FRAME_MODE"          => "A",
                "COMPOSITE_FRAME_TYPE"          => "AUTO",
                "DONT_INCLUDE_PRODUCT_IN_CACHE" => "N",
                "EXCLUDE_DELIVERIES"            => explode(',', $_REQUEST['exclude_deliveries']),
                "ID_TOVARA"                     => htmlspecialcharsbx($_REQUEST['productID']),
                "LOCATION_CODE"                 => htmlspecialcharsbx($_REQUEST['locationCode']),
                "PERSON_TYPE_ID"                => "",
                "TITLE"                         => htmlspecialcharsbx($_REQUEST['title'])
            )
        );
        die();
        break;
}

echo json_encode($answer);