<?php

namespace Aristov\VRegions;

use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class Tools{

    public static $moduleID = 'aristov.vregions';

    public static function makeText($text, $aReplace = null){
        $s = $text;

        $regionArr = Array();
        foreach ($_SESSION["VREGIONS_REGION"] as $code => $value){
            if (strpos($code, '~') === 0){
                continue;
            }

            if (is_array($value) && $value['TEXT']){
                if ($value['TYPE'] && $value['TYPE'] == 'HTML'){
                    $value = $value['TEXT'];
                } else{
                    $value = $value['TEXT'];
                }
            }

            // если и теперь $value массив, то это множественное свойство
            if (is_array($value)){
                $value = implode(',', $value);
            }

            $regionArr["#VREGION_".$code."#"]  = html_entity_decode($value);
            $regionArr["#~VREGION_".$code."#"] = $value;
        }

        if (is_array($aReplace)){
            $aReplace = array_merge($aReplace, $regionArr);
        } else{
            $aReplace = $regionArr;
        }

        if ($aReplace !== null && is_array($aReplace)){
            foreach ($aReplace as $search => $replace)
                $s = str_replace($search, $replace, $s);
        }

        return $s;
    }

    public static function ifRegionIsDefault($reginID = null){
        if (!$reginID){
            $reginID = $_SESSION["VREGIONS_REGION"]["ID"];
        }

        $answer = false;

        if ($reginID == $_SESSION["VREGIONS_DEFAULT"]["ID"]){
            $answer = true;
        }

        return $answer;
    }

    function sitemap_gen($sitemap_path, $site_url, $new_path = ''){
        if (substr($sitemap_path, 0, 1) != '/'){
            $sitemap_path = '/'.$sitemap_path;
        }
        $sitemap_path = $_SERVER["DOCUMENT_ROOT"].$sitemap_path;

        if ($new_path){
            if (substr($new_path, 0, 1) != '/'){
                $new_path = '/'.$new_path;
            }
            $new_path = $_SERVER["DOCUMENT_ROOT"].$new_path;
        } else{
            $new_path = str_replace('.xml', '.php', $sitemap_path);
        }

        $dyn_sitemap = '<?'.PHP_EOL.'$host = preg_replace("/\:\d+/is", "", $_SERVER["HTTP_HOST"]);'.PHP_EOL.
            'if ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https") || (isset($_SERVER["HTTP_X_FORWARDED_SCHEME"]) && $_SERVER["HTTP_X_FORWARDED_SCHEME"] == "https")){'.PHP_EOL.
            '	$http = "https";'.PHP_EOL.
            '}'.PHP_EOL.
            'else{'.PHP_EOL.
            '	$http = "http";'.PHP_EOL.
            '}'.PHP_EOL.
            'header("Content-Type: text/xml");'.PHP_EOL;

        $sitemap = file_get_contents($sitemap_path);
        if (!$sitemap){
            return false;
        }

        // замены
        $search  = Array(
            'http://'.$site_url,
            'https://'.$site_url,
            $site_url,
        );
        $replace = Array(
            '<?=$http."://".$host;?>',
            '<?=$http."://".$host;?>',
            '<?=$host;?>',
        );
        $sitemap = str_replace($search, $replace, $sitemap);

        // замена <?xml
        $sitemap = preg_replace('/(\<\?xml[^\>]+\>)/i', "echo '$1';?>".PHP_EOL, $sitemap);

        $dyn_sitemap .= $sitemap;

        if (!file_put_contents($new_path, $dyn_sitemap)){
            return false;
        }

        return true;
    }

    function generate_robots($site_url, $only_https, $phpFile = false){
        $answer            = Array();
        $answer["success"] = false;

        $robots_path = $_SERVER["DOCUMENT_ROOT"].'/robots.txt';
        $robots_file = file_get_contents($robots_path);
        if (!strlen($robots_file)){
            $answer["message"] = 'NO_ROBOTS';

            return $answer;
        }

        if (strpos($robots_file, '$host = $_SERVER["HTTP_HOST"];') === false){ // inache schitaem chto uzhe nazhimali knopku
            if (!$only_https){
                $robots_file = '<?$host = $_SERVER["HTTP_HOST"];
$host = preg_replace("/\:\d+/is", "", $host);
if ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https")){
	$http = "https";
}
else{
	$http = "http";
}
header("Content-Type: text/plain");?>'.PHP_EOL.$robots_file;
            } else{
                $robots_file = '<?$host = $_SERVER["HTTP_HOST"];
$host = preg_replace("/\:\d+/is", "", $host);
$http = "https";
header("Content-Type: text/plain");?>'.PHP_EOL.$robots_file;
            }
        }

        // прячем php
        $search      = Array(
            '<?=$http?>:',
            '<?=$http?>:',
            '<?=$host.PHP_EOL;?>'
        );
        $replace     = Array(
            '<?=$ht_tp?>:',
            '<?=$ht_tp?>:',
            '<?=$ho_st.PHP_EOL;?>'
        );
        $robots_file = str_replace($search, $replace, $robots_file);

        $search      = Array(
            'http:',
            'https:',
            $site_url
        );
        $replace     = Array(
            '<?=$http?>:',
            '<?=$http?>:',
            '<?=$host.PHP_EOL;?>'
        );
        $robots_file = str_replace($search, $replace, $robots_file);

        // возвращаем php
        $search      = Array(
            '<?=$ht_tp?>:',
            '<?=$ht_tp?>:',
            '<?=$ho_st.PHP_EOL;?>'
        );
        $replace     = Array(
            '<?=$http?>:',
            '<?=$http?>:',
            '<?=$host.PHP_EOL;?>'
        );
        $robots_file = str_replace($search, $replace, $robots_file);

        // ubiraem lishniy perenos
        $search      = Array('<?=$host.PHP_EOL;?>/');
        $replace     = Array('<?=$host;?>/');
        $robots_file = str_replace($search, $replace, $robots_file);

        if ($phpFile){
            $robotsPathWrite = $_SERVER["DOCUMENT_ROOT"].'/robots.php';
        } else{
            $robotsPathWrite = $_SERVER["DOCUMENT_ROOT"].'/robots.txt';
        }
        if (!file_put_contents($robotsPathWrite, $robots_file)){
            $answer["message"] = 'CANNOT_WRITE';

            return $answer;
        }

        $answer["success"] = true;

        return $answer;
    }

    public static function replaceVarsInMeta($dopReplace = Array()){
        global $APPLICATION;
        if ($APPLICATION->GetDirProperty("title")){
            $APPLICATION->SetDirProperty("title", static::makeText($APPLICATION->GetDirProperty("title"), $dopReplace));
        }
        if ($APPLICATION->GetDirProperty("description")){
            $APPLICATION->SetDirProperty("description", static::makeText($APPLICATION->GetDirProperty("description"), $dopReplace));
        }
        if ($APPLICATION->GetDirProperty("keywords")){
            $APPLICATION->SetDirProperty("keywords", static::makeText($APPLICATION->GetDirProperty("keywords"), $dopReplace));
        }
        if ($APPLICATION->GetDirProperty("canonical")){
            $APPLICATION->SetDirProperty("canonical", static::makeText($APPLICATION->GetDirProperty("canonical"), $dopReplace));
        }
        if ($APPLICATION->GetPageProperty("title")){
            $APPLICATION->SetPageProperty("title", static::makeText($APPLICATION->GetPageProperty("title"), $dopReplace));
        }
        if ($APPLICATION->GetPageProperty("description")){
            $APPLICATION->SetPageProperty("description", static::makeText($APPLICATION->GetPageProperty("description"), $dopReplace));
        }
        if ($APPLICATION->GetPageProperty("keywords")){
            $APPLICATION->SetPageProperty("keywords", static::makeText($APPLICATION->GetPageProperty("keywords"), $dopReplace));
        }
        if ($APPLICATION->GetPageProperty("canonical")){
            $APPLICATION->SetPageProperty("canonical", static::makeText($APPLICATION->GetPageProperty("canonical"), $dopReplace));
        }
        if ($APPLICATION->GetTitle()){
            $APPLICATION->SetTitle(static::makeText($APPLICATION->GetTitle(), $dopReplace));
        }
    }

    function generate_robots_difficult($prop, $makePhpFile, $replaceSiteAddress = false){
        $answer            = Array();
        $answer["success"] = false;

        if (!$prop){
            $answer['message'] = 'NO_CODE_FOR_PROP';

            return $answer;
        }

        $ext = 'txt';
        if ($makePhpFile){
            $ext = 'php';
        }
        $robots_path = $_SERVER["DOCUMENT_ROOT"].'/robots.'.$ext;

        $replaceSiteAddressBlock = '';
        if ($replaceSiteAddress){
            $replaceSiteAddressBlock = '$host = $_SERVER["HTTP_HOST"];
$host = preg_replace("/\:\d+/is", "", $host);
if ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https")){
    $http = "https";
} else{
    $http = "http";
}
$robots = preg_replace("/https?\:\/\/[^\/]+\.[a-z]{2,3}/", $http."://".$host, $robots);';
        }

        $robots_file = '<? header("Content-Type: text/plain");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$robots  = html_entity_decode($_SESSION["VREGIONS_REGION"]["ROBOTS_TXT"]["TEXT"]);
$search  = [
    "<br>",
    "\n",
];
$replace = [
    PHP_EOL,
    PHP_EOL,
];
$robots  = str_replace($search, $replace, $robots);

'.$replaceSiteAddressBlock.'

echo $robots;
?>';

        if (!file_put_contents($robots_path, $robots_file)){
            $answer["message"] = 'CANNOT_WRITE';

            return $answer;
        }

        $answer["success"] = true;

        return $answer;
    }

    public static function getLocationByIP($ip){
        $vregions_php_geoposition_tool = static::getModuleOption("vregions_php_geoposition_tool", "sxgeo");

        if ($vregions_php_geoposition_tool == 'sxgeo'){
            mb_internal_encoding("cp-1251");
            $SxGeo       = new \Aristov\VRegions\SxGeo($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/aristov.vregions/lib/SxGeoCity.dat", SXGEO_BATCH | SXGEO_MEMORY);
            $locationArr = $SxGeo->get($ip);
            mb_internal_encoding(LANG_CHARSET);

            $cityID      = $locationArr["city"]["id"];
            $lat         = $locationArr["city"]["lat"];
            $lon         = $locationArr["city"]["lon"];
            $cityNameRu  = iconv('CP1251', LANG_CHARSET, $locationArr["city"]["name_ru"]);
            $cityNameEn  = $locationArr["city"]["name_en"];
            $countryCode = $locationArr["country"]["iso"];
        }

        if ($vregions_php_geoposition_tool == 'ipgeobase'){
            $locationArr = \Aristov\Vregions\IpGeoBase\AVIpGeoBase::getInfoAboutIP($ip);

            $cityID      = $locationArr["id"];
            $lat         = $locationArr["lat"];
            $lon         = $locationArr["lon"];
            $cityNameRu  = $locationArr["cityName"];
            $countryCode = $locationArr["countyCode"];
        }

        $answer = Array();
        if (isset($cityID) && $cityID){
            $answer["city"]["id"] = $cityID; // obratnaya sovmestimost
        }
        if (isset($lat) && $lat){
            $answer["city"]["lat"] = $lat; // obratnaya sovmestimost
        }
        if (isset($lon) && $lon){
            $answer["city"]["lon"] = $lon; // obratnaya sovmestimost
        }
        if (isset($cityNameRu) && $cityNameRu){
            $answer["city"]["name_ru"] = $cityNameRu; // obratnaya sovmestimost
        }
        if (isset($cityNameEn) && $cityNameEn){
            $answer["city"]["name_en"] = $cityNameEn; // obratnaya sovmestimost
        }
        if (isset($cityNameRu) && $cityNameRu){
            $answer["city"]["name_ru"] = $cityNameRu; // obratnaya sovmestimost
        }
        if (isset($countryCode) && $countryCode){
            $answer["country"]["iso"] = $countryCode; // obratnaya sovmestimost
        }

        return $answer;
    }

    public static function getUserIP(){
        $userIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
        if (strpos($userIP, ',')){
            list($userIP,) = explode(',', $userIP);
        }

        if (!$userIP){
            $userIP = $_SERVER['HTTP_X_REAL_IP'];
            if (!$userIP){
                $userIP = $_SERVER['REMOTE_ADDR'];
            }
        }

        return $userIP;
    }

    public static function getClosestToCoordsRegion($userLatitude, $userLongitude){
        $IBLOCK_ID              = static::getModuleOption("vregions_iblock_id");
        $PROPERTY_REGION_CENTRE = static::getModuleOption("vregions_iblock_region_centre_prop");
        \CModule::IncludeModule("iblock");

        $minDist = false;
        $region  = false;

        $res = \CIBlockElement::GetList(
            Array(),
            Array(
                "IBLOCK_ID" => $IBLOCK_ID,
                "ACTIVE"    => "Y"
            ),
            false,
            false,
            Array(
                "NAME",
                "CODE",
                "SORT",
                "PROPERTY_".$PROPERTY_REGION_CENTRE,
                "PROPERTY_DONT_REDIRECT_HERE_AUTO",
                "PROPERTY_HTTP_PROTOCOL",
                "PROPERTY_FULL_URL",
                "PROPERTY_WWW",
            )
        );
        while($ob = $res->GetNextElement()){
            $arFields = $ob->GetFields();
            // chtoby lishniy raz ne muchat'sya s kodirovkami na khuyovykh saytakh
            unset($arFields["~NAME"]);

            if (static::getModuleOption("dont_convert_encoding") != 'Y'){
                if (LANG_CHARSET != 'UTF-8'){
                    $arFields["NAME"] = iconv(LANG_CHARSET, 'UTF-8', $arFields["NAME"]);
                }
            }

            $arFields['DONT_REDIRECT_HERE_AUTO'] = $arFields['PROPERTY_DONT_REDIRECT_HERE_AUTO_VALUE'] == 'Y' ? 1 : 0;

            $coords    = explode(",", $arFields["PROPERTY_".$PROPERTY_REGION_CENTRE."_VALUE"]);
            $latitude  = $coords[0];
            $longitude = $coords[1];

            $dist = static::calculateTheDistance($userLatitude, $userLongitude, $latitude, $longitude);

            if ($minDist === false){
                $minDist = $dist;
                $region  = $arFields;
            } else{
                if ($minDist > $dist){
                    $minDist = $dist;
                    $region  = $arFields;
                }
            }
        }

        return $region;
    }

    // rasstojanie na globuse
    public static function calculateTheDistance($yA, $xA, $yB, $xB){
        $yA           = $yA + 0;
        $xA           = $xA + 0;
        $yB           = $yB + 0;
        $xB           = $xB + 0;
        $EARTH_RADIUS = 6372795;
        $lat1         = $yA * M_PI / 180;
        $lat2         = $yB * M_PI / 180;
        $long1        = $xA * M_PI / 180;
        $long2        = $xB * M_PI / 180;

        $cl1    = cos($lat1);
        $cl2    = cos($lat2);
        $sl1    = sin($lat1);
        $sl2    = sin($lat2);
        $delta  = $long2 - $long1;
        $cdelta = cos($delta);
        $sdelta = sin($delta);

        $y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
        $x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;

        $ad   = atan2($y, $x);
        $dist = $ad * $EARTH_RADIUS;

        return $dist;
    }

    /**
     * Najti region po maske nazvanija
     * @param string $mask
     * @return array
     */
    public static function findRegionByNameMask($mask){
        \CModule::IncludeModule("iblock");
        $IBLOCK_ID = static::getModuleOption("vregions_iblock_id");

        $regions = Array();

        $res = \CIBlockElement::GetList(
            Array(),
            Array(
                "NAME"      => '%'.$mask.'%',
                "IBLOCK_ID" => $IBLOCK_ID,
                "ACTIVE"    => "Y"
            ),
            false,
            false,
            Array(
                "NAME",
                "CODE",
                "PROPERTY_HTTP_PROTOCOL",
            )
        );
        while($ob = $res->GetNextElement()){
            $arFields = $ob->GetFields();

            // chtoby lishniy raz ne muchat'sya s kodirovkami na khuyovykh saytakh
            unset($arFields["~NAME"]);

            if (LANG_CHARSET != 'UTF-8'){
                $arFields["NAME"] = iconv(LANG_CHARSET, 'UTF-8', $arFields["NAME"]);
            }

            $arFields["HREF"] = \Aristov\Vregions\Tools::generateRegionLink($arFields["CODE"], $arFields["PROPERTY_HTTP_PROTOCOL_VALUE"]);

            $regions[] = $arFields;
        }

        return $regions;
    }

    /**
     * Najti mestopolozhenie v Bitrikse po maske nazvanija
     *
     * @param string $mask
     * @return array
     */
    public static function findBitrixLocationByNameMask($mask){
        $cities = Array();
        $ids    = Array(); // chtoby ubrat' dubljazh

        if (\CModule::IncludeModule("sale")){
            // snazhala sopostovlyaem po imeni regiona
            $db_vars = \CSaleLocation::GetList(
                array(
                    "SORT" => "ASC",
                ),
                array(
                    '%CITY_NAME_LANG' => $mask
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

                        $cities[] = $location;
                        $ids[]    = $location["ID"];
                    }
                }
            }
        }

        return $cities;
    }

    public static function generateRegionLink($code, $protocol = '', $regionFullUrlProp = false, $www = false, $VREGION_DEFAULT = false, $domainInfo = false){
        if (!strlen($code)){
            return '';
        }
        if ($VREGION_DEFAULT === false){
            $VREGION_DEFAULT = static::getModuleOption("vregions_default");
        }

        if ($domainInfo === false){
            $domainInfo = static::getCurrentSiteMainDomainInfo();
        }

        if ($www !== false){
            $www = $www ? $www.'.' : '';
        } else{
            $www = $domainInfo['www'] ? $domainInfo['www'].'.' : '';
        }
        $domain = $domainInfo['domain_without_regions'];

        if ($regionFullUrlProp === false){ // для ускорения
            $regionFullUrlProp = static::getRegionFullUrlProp($code);
        }

        if (!strlen($protocol)){
            $protocol = static::getRegionProtocolProp($code);
        }
        $href = ($protocol ?: 'http')."://";

        if (!$regionFullUrlProp){
            $href .= $www;
            if ($code != $VREGION_DEFAULT){
                $href .= $code.".";
            }
            $href .= $domain;
        } else{
            $href .= $regionFullUrlProp;
        }

        $href = preg_replace('/\:\d+/is', "", $href); // ubiraem port

        return $href;
    }

    public static function getRegionFullUrlProp($code){
        if (!$code){
            return false;
        }
        if (!\CModule::IncludeModule("iblock")){
            return false;
        }

        $iblockID = static::getModuleOption("vregions_iblock_id");

        $res = \CIBlockElement::GetList(
            Array(
                "SORT" => "ASC"
            ),
            Array(
                'IBLOCK_ID' => $iblockID,
                'CODE'      => $code
            ),
            false,
            false,
            Array('PROPERTY_FULL_URL')
        );
        while($ob = $res->GetNextElement()){
            $arFields = $ob->GetFields();
            if ($arFields['PROPERTY_FULL_URL_VALUE']){
                return $arFields['PROPERTY_FULL_URL_VALUE'];
            }
        }

        return false;
    }

    public static function getRegionProtocolProp($code){
        if (!$code){
            return false;
        }
        if (!\CModule::IncludeModule("iblock")){
            return false;
        }

        $iblockID = static::getModuleOption("vregions_iblock_id");

        $res = \CIBlockElement::GetList(
            Array(
                "SORT" => "ASC"
            ),
            Array(
                'IBLOCK_ID' => $iblockID,
                'CODE'      => $code
            ),
            false,
            false,
            Array('PROPERTY_HTTP_PROTOCOL')
        );
        while($arFields = $res->GetNext(true, false)){
            if ($arFields['PROPERTY_HTTP_PROTOCOL_VALUE']){
                return $arFields['PROPERTY_HTTP_PROTOCOL_VALUE'];
            }
        }

        return false;
    }

    public static function getCurrentSiteMainDomainInfo(){
        $subdomainLevel = intval(static::getModuleOption("vregions_subdomain_level", 3));
        if (!$subdomainLevel){
            $subdomainLevel = 3;
        }

        $serverName = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        $serverName = preg_replace('/\:\d+/is', '', $serverName);
        $hostArr    = explode(".", $serverName);

        $www = "";
        if ($hostArr[0] == "www"){
            $www = "www";
        }

        $domain = "";
        for ($i = $subdomainLevel - 1; $i > 0; $i--){
            $domain .= $hostArr[count($hostArr) - $i];
            if ($i != 1){
                $domain .= ".";
            }
        }

        return Array(
            'www'                    => $www,
            'www_with_dot'           => $www ? $www.'.' : '',
            'domain_without_regions' => $domain,
            'server_name'            => $serverName,
            'domains'                => $hostArr,
        );
    }

    // poluchit' kolichestvo tovara na opredeljonnom sklade
    public static function getProductQuantity($productID, $skladID = ''){
        if (!\CModule::IncludeModule("catalog")){
            return false;
        }
        $quantity = 0;

        // kolichestvo tovara
        $rsStore = \CCatalogStoreProduct::GetList(
            array(),
            array(
                'PRODUCT_ID' => $productID,
            ),
            false,
            false,
            array()
        );
        while($arStore = $rsStore->Fetch()){
            if ($skladID){ // esli nuzhen opredeljonnyj sklad
                if ($arStore["STORE_ID"] == $skladID){
                    $quantity += $arStore["AMOUNT"];
                }
            } else{
                $quantity += $arStore["AMOUNT"];
            }
        }

        // est' li u tovara torgovye predlozhenija (esli est' prosummiruem kolichestvo)
        $offersExist = \CCatalogSKU::getExistOffers(Array($productID));
        if ($offersExist[$productID]){
            $offersRes = \CCatalogSKU::getOffersList(Array($productID));
            // summiruem k kolichestvu kolichestvo torgovyh predlozhenij
            foreach ($offersRes[$productID] as $offerID => $offerAr){
                $rsStore = \CCatalogStoreProduct::GetList(
                    array(),
                    array(
                        'PRODUCT_ID' => $offerID,
                    ),
                    false,
                    false,
                    array()
                );
                while($arStore = $rsStore->Fetch()){
                    if ($skladID){ // esli nuzhen opredeljonnyj sklad
                        if ($arStore["STORE_ID"] == $skladID){
                            $quantity += $arStore["AMOUNT"];
                        }
                    } else{
                        $quantity += $arStore["AMOUNT"];
                    }
                }
            }
        }

        return $quantity;
    }

    public static function ifPhpInTxtWorks(){
        // sohranjaem fajl dlja proverki
        file_put_contents($_SERVER["DOCUMENT_ROOT"].'/php_in_txt_test.txt', '<?php echo "ololo"; ?>');
        // sama proverka
        $content = file_get_contents('http://'.$_SERVER["HTTP_HOST"].'/php_in_txt_test.txt');
        if ($content == 'ololo'){
            return true;
        }

        return false;
    }

    public static function getRegionByCode($code){
        \CModule::IncludeModule("iblock");
        $IBLOCK_ID = static::getModuleOption("vregions_iblock_id");

        $res = \CIBlockElement::GetList(
            Array(),
            Array(
                "CODE"      => $code,
                "IBLOCK_ID" => $IBLOCK_ID,
                "ACTIVE"    => "Y"
            ),
            false,
            false,
            Array(
                "ID",
                "IBLOCK_ID",
                "NAME",
                "CODE",
            )
        );
        if ($ob = $res->GetNextElement()){
            $arFields               = $ob->GetFields();
            $arFields['PROPERTIES'] = $ob->GetProperties();

            if (LANG_CHARSET != 'UTF-8'){
                $arFields["NAME"] = iconv(LANG_CHARSET, 'UTF-8', $arFields["NAME"]);
            }

            return $arFields;
        }

        return false;
    }

    public static function getDescriptionFromSpecialIblockByUrl($textsIblockID, $urlPropertyCode = 'URL', $regionPropertyCode = 'REGION_ID', $url = ''){
        if (!\CModule::IncludeModule("catalog")){
            return false;
        }
        if (!$url){
            $url = $_SERVER['SCRIPT_URL'];
        }

        $res = \CIBlockElement::GetList(
            Array(
                "SORT" => "ASC"
            ),
            Array(
                'IBLOCK_ID'                     => $textsIblockID,
                'ACTIVE'                        => 'Y',
                'PROPERTY_'.$urlPropertyCode    => $url,
                'PROPERTY_'.$regionPropertyCode => $_SESSION['VREGIONS_REGION']["ID"],
            ),
            false,
            false,
            Array()
        );
        if ($ob = $res->GetNextElement()){
            $arFields = $ob->GetFields();

            if ($arFields["DETAIL_TEXT"]){
                return $arFields["DETAIL_TEXT"];
            }
        }

        return false;
    }

    public static function isWorkingOnOnlyOneDomain(){
        return (static::getModuleOption("vregions_work_on_one_domain") == "Y") ? true : false;
    }

    public static function setManualVregion($regionCode){
        $_SESSION['VREGIONS_MANUAL'] = Array(
            'CODE' => $regionCode
        );

        return true;
    }

    public static function getSiteIDByHost(){
        $id = false;

        $cache = \Bitrix\Main\Data\Cache::createInstance();
        if ($cache->initCache(7200, "getSiteIDByHost".$_SERVER["HTTP_HOST"])){
            $id = $cache->getVars();
        } elseif ($cache->startDataCache()){
            if (!class_exists('CMainPage')){
                include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/mainpage.php");
            }

            $CMainPage = new \CMainPage();
            $id        = $CMainPage->GetSiteByHost();

            $cache->endDataCache($id);
        }

        return $id;
    }

    public static function setModuleOption($optionCode, $value, $siteID = false)
    {
        if (!$siteID) {
            $siteID = static::getSiteIDByHost();
        }

        // todo тут можно поставить очистку кеша и тогда избавиться от $withoutCache в геттере

        return \COption::SetOptionString(static::$moduleID, $optionCode, $value, false, $siteID);
    }

    public static function getModuleOption($optionCode, $default = '', $withoutCache = false, $siteID = false)
    {
        $answer = $default;

        if (!$siteID) {
            $siteID = static::getSiteIDByHost();
        }
        
        if ($withoutCache){
            return $answer = \COption::GetOptionString(static::$moduleID, $optionCode, $default, $siteID);
        }

        $cache = \Bitrix\Main\Data\Cache::createInstance();
        if ($cache->initCache(2592000, "Aristov_VRegions_Tools_getModuleOption".$optionCode.$siteID)){
            $answer = $cache->getVars();
        } elseif ($cache->startDataCache()){
            $answer = \COption::GetOptionString(static::$moduleID, $optionCode, $default, $siteID);
            $cache->endDataCache($answer);
        }

        return $answer;
    }

    public static function redirectByRegionName($regionName){
        \CModule::IncludeModule('iblock');

        $domainInfo       = static::getCurrentSiteMainDomainInfo();
        $siteName         = $domainInfo['domain_without_regions'];
        $protocol         = 'http';
        $domains          = $domainInfo['domains'];
        $vregions_default = static::getModuleOption("vregions_default");

        $subdomainLevel = intval(static::getModuleOption("vregions_subdomain_level")) ?: 3;
        if (count($domains) >= $subdomainLevel){
            $currentSubdomain = $domains[0];
        } else{
            $currentSubdomain = '';
        }
        if ($currentSubdomain == 'www'){
            $currentSubdomain = '';
        }

        $selectedRegionCode = '';
        $res                = \CIBlockElement::GetList(Array(), Array(
            'IBLOCK_ID' => static::getModuleOption("vregions_iblock_id"),
            'NAME'      => $regionName,
            'ACTIVE'    => 'Y'
        ), false, false, Array(
            'ID',
            'NAME',
            'CODE'
        ));
        if ($ob = $res->GetNextElement()){
            $arFields           = $ob->GetFields();
            $selectedRegionCode = $arFields['CODE'];
        }
        if ($selectedRegionCode == $vregions_default){
            $selectedRegionCode = '';
        }

        if ($currentSubdomain != $selectedRegionCode){
            header('Location: '.$protocol.'://'.$selectedRegionCode.($selectedRegionCode ? '.' : '').$siteName.$_SERVER['REQUEST_URI']);
        }
    }

    public static function setProductsPriceByAnotherPrice($fromPriceID, $toPriceID, $multiplier = 1, $productsFilter = []){
        \CModule::IncludeModule('iblock');
        \CModule::IncludeModule('catalog');

        $handledProducts = [];

        $res = \CIBlockElement::GetList(
            Array(
                "SORT" => "ASC"
            ),
            $productsFilter,
            false,
            false,
            Array(
                'ID',
                'NAME',
                'IBLOCK_ID',
                'IBLOCK_SECTION_ID',
                'catalog_GROUP_'.$fromPriceID,
            )
        );
        while($ob = $res->GetNextElement()){
            $arFields = $ob->GetFields();

            if (!$multiplier){
                $dbSections = \CIBlockSection::GetList(
                    Array(
                        "SORT" => "ASC"
                    ),
                    Array(
                        'IBLOCK_ID' => $arFields['IBLOCK_ID'],
                        'ID'        => $arFields['IBLOCK_SECTION_ID'],
                    ),
                    false,
                    Array(
                        'ID',
                        'IBLOCK_ID',
                        'UF_VR_PRICE_MUL',
                    )
                );
                while($arSection = $dbSections->GetNext()){
                    if ($arSection['UF_VR_PRICE_MUL']){
                        $multiplier = $arSection['UF_VR_PRICE_MUL'];
                    }
                }
            }

            if (!$multiplier){
                $multiplier = 1;
            }

            // устанавливаем цену
            $newPriceVal   = $arFields['CATALOG_PRICE_'.$fromPriceID] * $multiplier;
            $arPriceFields = Array(
                "PRODUCT_ID"       => $arFields['ID'],
                "CATALOG_GROUP_ID" => $toPriceID,
                "PRICE"            => $newPriceVal,
            );
            $priceRes      = \CPrice::GetList(
                array(),
                array(
                    "PRODUCT_ID"       => $arFields['ID'],
                    "CATALOG_GROUP_ID" => $toPriceID
                )
            );
            if ($priceArr = $priceRes->Fetch()){
                if (\CPrice::Update($priceArr["ID"], $arPriceFields)){
                    $handledProducts[] = array_merge($arFields, Array('NEW_PRICE_VALUE' => $newPriceVal));
                }
            } else{
                $arPriceFields['CURRENCY'] = $arFields['CATALOG_CURRENCY_'.$fromPriceID]; // обязательный параметр при создании
                if (\CPrice::Add($arPriceFields)){
                    $handledProducts[] = array_merge($arFields, Array('NEW_PRICE_VALUE' => $newPriceVal));
                }
            }
        }

        return $handledProducts;
    }

    public static function sitemapAgent(){
        foreach (unserialize(static::getModuleOption('sitemap_files', '', true)) as $path){
            if (static::sitemap_gen($path, static::getModuleOption('sitemap_domain', '', true))){

            }
        }

        return '\Aristov\VRegions\Tools::sitemapAgent();';
    }
}