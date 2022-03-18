<?
define('NO_KEEP_STATISTIC', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
IncludeModuleLangFile(__FILE__);

if (!check_bitrix_sessid()){
    return;
}

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

CUtil::JSPostUnescape();

$answer            = Array();
$answer["success"] = 0;
// $answer = array_merge($answer, $_REQUEST);
// $answer = array_merge($answer, $_SERVER);
$action                   = $_REQUEST["action"];
$answer["request-action"] = $action;

$IBLOCK_ID     = COption::GetOptionString("aristov.vregions", "vregions_iblock_id");
$defaultRegion = COption::GetOptionString("aristov.vregions", "vregions_default");

$cookieLifetime = COption::GetOptionString("aristov.vregions", "vregions_cookie_lifetime");
if ($cookieLifetime === ""){
    $cookieLifetime = 3600 * 24 * 30 * 2;
} else{
    $cookieLifetime = intval($cookieLifetime);
}

// na kakom urovne iskat poddomen
$subdomainLevel = intval(\COption::GetOptionString("aristov.vregions", "vregions_subdomain_level"));
if (!$subdomainLevel){
    $subdomainLevel = 3; // default
}

$domainInfo = \Aristov\Vregions\Tools::getCurrentSiteMainDomainInfo();

$domains           = $domainInfo['domains'];
$www               = $domainInfo['www_with_dot'];
$siteURL           = $domainInfo['domain_without_regions'];
$answer['siteUrl'] = $siteURL;

// tekuschiy poddomen
$currentSubdomain           = $domains[count($domains) - $subdomainLevel];
$currentSubdomain           = $currentSubdomain == 'www' ? '' : $currentSubdomain;
$answer['currentSubdomain'] = $currentSubdomain;

$permit_redirect_always     = (\COption::GetOptionString("aristov.vregions", "vregions_auto_redirect") == 'Y' ? 1 : 0);
$needCookieRedirectOnlyMain = (\COption::GetOptionString("aristov.vregions", "vregions_auto_redirect_only_main") == "Y") ? 1 : 0;
$PROPERTY_LATITUDE          = \COption::GetOptionString("aristov.vregions", "vregions_iblock_latitude_prop");
$PROPERTY_LONGITUDE         = \COption::GetOptionString("aristov.vregions", "vregions_iblock_longitude_prop");
$PROPERTY_REGION_CENTRE     = \COption::GetOptionString("aristov.vregions", "vregions_iblock_region_centre_prop");
$permit_redirect            = (\COption::GetOptionString("aristov.vregions", "vregions_auto_geoposition_redirect_for_new") == "Y") ? 1 : 0;
$geoMethod                  = \COption::GetOptionString("aristov.vregions", "vregions_auto_geoposition_method");

$currentRegionCode = $_SESSION['VREGIONS_REGION']['CODE'];

if ($action == "check-auto-geo-ness"){ // todo rename to get-auto-geo-method
    if ($geoMethod){
        $answer["success"] = 1;
        $answer["method"]  = $geoMethod;
    }
}

if ($action == "get-php-coords"){
    if ($_SESSION["VREGIONS_PHP"]["city"]['lat'] && $_SESSION["VREGIONS_PHP"]["city"]['lon']){
        $answer["success"] = 1;
        $answer["lat"]     = $_SESSION["VREGIONS_PHP"]["city"]['lat'];
        $answer["lon"]     = $_SESSION["VREGIONS_PHP"]["city"]['lon'];
    } else{ // esli pochemu-to net etogo massiva
        $userIP = \Aristov\Vregions\Tools::getUserIP();
        $city   = \Aristov\Vregions\Tools::getLocationByIP($userIP);
        if ($city["city"]['lat'] && $city["city"]['lon']){
            $answer["success"] = 1;
            $answer["lat"]     = $city["city"]['lat'];
            $answer["lon"]     = $city["city"]['lon'];
        }
    }
}

if ($action == "get-closest-region"){
    $answer["redirect"] = false;

    $region                            = \Aristov\Vregions\Tools::getClosestToCoordsRegion($_REQUEST["latitude"], $_REQUEST["longitude"]);
    $answer['detected_closest_region'] = $region;

    $answer['dont_show_ask_window_if_already_on_needed_region'] = \Aristov\Vregions\Tools::getModuleOption('dont_show_ask_window_if_already_on_needed_region') == 'Y';

    $exCookie            = \VregionsAjaxHelper::getCookie();
    $answer["ex-cookie"] = $exCookie;

    if (!$exCookie){
        $regionCode = $region["CODE"];

        $newCookie = $regionCode ?: "";
        \VregionsAjaxHelper::setCookie($newCookie);
        $answer["cookie"] = $newCookie;
    } else{ // todo зачем вообще здесь брать запомненный регион?
        $regionCode = $region["CODE"];
        //        $regionCode = $exCookie;
        //        $region     = \Aristov\Vregions\Tools::getRegionByCode($exCookie);
    }

    $subdomain           = \VregionsAjaxHelper::makeSubdomainFromRegionCode($regionCode, $defaultRegion);
    $answer["subdomain"] = $subdomain;

    $answer["redirect"]          = \VregionsAjaxHelper::ifNeedToRedirect($region['DONT_REDIRECT_HERE_AUTO'], $exCookie);
    $answer['already_on_region'] = $region["CODE"] == $currentRegionCode;

    $answer["region"]           = $region["NAME"];
    $answer["region_code"]      = $region["CODE"]; // используется в js
    $answer["url_without_path"] = Aristov\Vregions\Tools::generateRegionLink($regionCode, $region["PROPERTY_HTTP_PROTOCOL_VALUE"], $region["PROPERTY_FULL_URL_VALUE"], $region["PROPERTY_WWW_VALUE"]);

    if (\Aristov\Vregions\Tools::isWorkingOnOnlyOneDomain()){
        \Aristov\Vregions\Tools::setManualVregion($regionCode);
        $answer["url_without_path"] = "http://".$www."".$siteURL."";
    }
}

// nuzhen li redirekt, esli v kukah est' opredeljonnyj regiona. Esli nuzhen, to kuda
if ($action == "prepare-for-redirect-by-region-code"){
    $regionCode = strip_tags($_REQUEST['code']);

    $region = \Aristov\Vregions\Tools::getRegionByCode($regionCode);

    if ($region['PROPERTIES']['DONT_REDIRECT_HERE_AUTO']['VALUE'] != 'Y'){
        if ($permit_redirect_always){
            if (!$needCookieRedirectOnlyMain || ($needCookieRedirectOnlyMain && $_SESSION['VREGIONS_REGION']['ID'] == $_SESSION['VREGIONS_DEFAULT']['ID'])){
                if ($_SESSION['VREGIONS_REGION']['CODE'] != $regionCode){
                    $answer["redirect"] = true;

                    $link            = \Aristov\Vregions\Tools::generateRegionLink($regionCode);
                    $answer['domen'] = $link;

                    if (\Aristov\Vregions\Tools::isWorkingOnOnlyOneDomain()){
                        \Aristov\Vregions\Tools::setManualVregion($regionCode);
                        $answer["url_without_path"] = "http://".$www."".$siteURL."";
                        $answer["redirect"]         = false;
                    }
                }
            }
        }
    }
}

if ($action == "set-cookie"){
    $newCookie = $_REQUEST["cookie"];

    if (\VregionsAjaxHelper::setCookie($newCookie)){
        if (\Aristov\Vregions\Tools::isWorkingOnOnlyOneDomain()){
            if (\Aristov\Vregions\Tools::setManualVregion($newCookie)){
                $answer["reload"]  = 1;
                $answer["success"] = 1;
            }
        } else{
            $answer["redirect"] = 1;
            $answer["success"]  = 1;
        }
    }
}

// todo a u menja eshhjo est' takoe dejstvie
if ($action == "change-city"){ // smenit gorod polzovatelya
    // todo podbirat podhodyzschyy region po koordinatam goroda
    $_SESSION["VREGIONS_PHP"]["city"]["name_ru"] = urldecode($_REQUEST["cityName"]);
    $answer["success"]                           = 1;
}

if ($action == "find-region-by-name-mask"){
    $answer["regions"] = \Aristov\Vregions\Tools::findRegionByNameMask($_REQUEST["mask"]);
//    if (empty($answer["regions"])){
//        $answer["regions"] = \Aristov\Vregions\Tools::findBitrixLocationByNameMask($_REQUEST["mask"]);
//    }
}

if ($action == "change-bitrix-location"){
    if (\CModule::IncludeModule("sale")){
        // snazhala sopostovlyaem po imeni regiona
        $db_vars = \CSaleLocation::GetList(
            array(
                "SORT" => "ASC",
            ),
            array(
                'ID' => $_REQUEST["id"]
            ),
            false,
            false,
            array()
        );
        if ($location = $db_vars->Fetch()){
            $answer["success"] = true;

            $_SESSION["VREGIONS_IM_LOCATION"]                  = $location;
            $_SESSION["VREGIONS_IM_LOCATION"]["LOCATION_CODE"] = \CSaleLocation::getLocationCODEbyID($_SESSION["VREGIONS_IM_LOCATION"]["ID"]);
            $_SESSION["VREGIONS_IM_LOCATION"]["SELECTED"]      = true;
        }
    }
}

if ($action == "get-saved-region"){
    $answer['region'] = \VregionsAjaxHelper::getCookie();
}

if ($action == "save-region"){
    $newCookie = $_REQUEST["region"] ?: "";
    if (\VregionsAjaxHelper::setCookie($newCookie)){
        $answer['success'] = true;
    }
}

echo json_encode($answer);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php")

?>
