<?

use Aristov\VRegions\Meta;
use Aristov\Vregions\Tools;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Localization;
use Bitrix\Main\Web\Cookie;

class VRegionsPageLoadHelper{

    static $MODULE_ID = "aristov.vregions";

    public static function getMaxRedirectCount(){
        return 3;
    }

    public static function isNeedCookieRedirect($currentRegionCode){
        if (\AristovVregionsHelper::isDemoEnd()){
            return false;
        }

        $needCookieRedirect         = (Tools::getModuleOption("vregions_auto_redirect") == "Y") ? 1 : 0;
        $needCookieRedirectOnlyMain = (Tools::getModuleOption("vregions_auto_redirect_only_main") == "Y") ? 1 : 0;
        $DEFAULT_REGION_CODE        = Tools::getModuleOption("vregions_default");

        if (!$needCookieRedirect){
            return false;
        }

        $subdomainCookie = static::getRegionCookie();
        if (!$subdomainCookie){
            return false;
        }

        if ($subdomainCookie == $currentRegionCode){
            return false;
        }

        if ($currentRegionCode == $DEFAULT_REGION_CODE){
            return true;
        }

        if (!$needCookieRedirectOnlyMain){
            return true;
        }

        return false;
    }

    public static function isThereIsTooMuchSubdomains(){
        if (\AristovVregionsHelper::isDemoEnd()){
            return false;
        }

        $subdomainLevel = static::getSubDomainsLevel();
        $domainInfo     = Tools::getCurrentSiteMainDomainInfo();
        $domain         = $domainInfo['server_name'];
        $domains        = explode(".", $domain);
        $countDomains   = count($domains);

        if ($domains[0] == 'www'){
            $countDomains = $countDomains - 1;
        }

        if ($countDomains > $subdomainLevel){
            return true;
        }

        return false;
    }

    public static function getRegionCookie(){
        if (\AristovVregionsHelper::isDemoEnd()){
            return false;
        }

        global $APPLICATION;
        $subdomainCookie = $APPLICATION->get_cookie("VREGION_SUBDOMAIN");

        if (!strlen($subdomainCookie)){
            return false;
        }

        return $subdomainCookie;
    }

    public static function redirectToRegionDomain($regionCode){
        if (\AristovVregionsHelper::isDemoEnd()){
            return false;
        }

        if (Tools::isWorkingOnOnlyOneDomain()){
            return false;
        }

        $res = \Bitrix\Main\Loader::includeSharewareModule("aristov.vregions");
        if ($res === 0 || $res === 3){
            return false;
        }

        $link = Tools::generateRegionLink($regionCode);
        static::redirectByHeaderString($link);
    }

    public static function handleRegionDetectError(){
        if (\AristovVregionsHelper::isDemoEnd()){
            return false;
        }

        $handleErrors        = (Tools::getModuleOption("vregions_error_handle") == "Y") ? 1 : 0;
        $errorPage           = Tools::getModuleOption("vregions_error_page");
        $DEFAULT_REGION_CODE = Tools::getModuleOption("vregions_default");

        $_SESSION["VREGIONS_REGION"] = $_SESSION["VREGIONS_DEFAULT"]; // chtoby hot' kakaja-to infa pokazyvalas'

        if ($handleErrors){
            if (strlen($errorPage)){
                static::redirectByHeaderString($errorPage);
            } else{
                static::redirectByHeaderString(Tools::generateRegionLink($DEFAULT_REGION_CODE));
            }
        } else{
            // esli ne nado obrabatyvat' oshibki, to nichego ne delaem
        }
    }

    public static function getSubDomainsLevel(){
        if (\AristovVregionsHelper::isDemoEnd()){
            return 3;
        }

        $subdomainLevel = intval(Tools::getModuleOption("vregions_subdomain_level"));
        if (!$subdomainLevel){
            $subdomainLevel = 3; // default
        }

        return $subdomainLevel;
    }

    public static function getRegionCodeOfCurrentDomain(){
        if (Tools::isWorkingOnOnlyOneDomain() && $_SESSION['VREGIONS_MANUAL'] && $_SESSION['VREGIONS_MANUAL']['CODE']){
            return $_SESSION['VREGIONS_MANUAL']['CODE'];
        }

        $res = \Bitrix\Main\Loader::includeSharewareModule("aristov.vregions");
        if ($res === 0 || $res === 3){
            return false;
        }

        $subdomainLevel      = static::getSubDomainsLevel();
        $DEFAULT_REGION_CODE = Tools::getModuleOption("vregions_default");
        $domainInfo          = Tools::getCurrentSiteMainDomainInfo();
        $domain              = $domainInfo['server_name'];

        $cache = Cache::createInstance();
        if ($cache->initCache(7200, 'aristov_vregions_'.$domain.'_region_code', '/')){
            $regionCode = $cache->getVars();
        } elseif ($cache->startDataCache()){
            $domains = explode(".", $domain);

            $regionCodeByFullUrlProp                                    = static::getRegionCodeByFullUrlProp($domain);
            $_SESSION['VREGIONS_DEBUG']['REGION_CODE_BY_FULL_URL_PROP'] = $regionCodeByFullUrlProp;

            if (!$regionCodeByFullUrlProp){
                $regionCode = $domains[count($domains) - ($subdomainLevel)]; // poddomen
                // proverka na sluchaj esli poddomena voobshche net
                if (!$regionCode || ($regionCode == $domains[count($domains) - ($subdomainLevel - 1)] && count($domains) == $subdomainLevel - 1)){
                    if ($DEFAULT_REGION_CODE){
                        $regionCode = $DEFAULT_REGION_CODE;
                    } else{
                        $regionCode = '';
                    }
                }
            } else{
                $regionCode = $regionCodeByFullUrlProp;
            }

            if ($regionCode == 'www'){ // inache pri rabote po www vsyo upadet
                $regionCode = $DEFAULT_REGION_CODE;
            }

            if (function_exists('idn_to_utf8')){
                $_SESSION['VREGIONS_DEBUG']['DOMAIN_FROM_IDN_TO_UTF8'] = idn_to_utf8($regionCode);
                if ($regionCode != idn_to_utf8($regionCode)){ // cyrillic
                    $regionCode = idn_to_utf8($regionCode);
                }
            }
            $cache->endDataCache($regionCode);
        }

        return $regionCode;
    }

    public static function getRegionCodeByFullUrlProp($domain){
        if (!\CModule::IncludeModule("iblock")){
            return false;
        }
        if (\AristovVregionsHelper::isDemoEnd()){
            return false;
        }

        $res = \Bitrix\Main\Loader::includeSharewareModule("aristov.vregions");
        if ($res === 0 || $res === 3){
            return false;
        }

        $_SESSION['VREGIONS_DEBUG']['DOMAIN_FOR_FULL_URL_PROP'] = $domain;

        $iblockID = Tools::getModuleOption("vregions_iblock_id");

        $res = \CIBlockElement::GetList(
            Array(
                "SORT" => "ASC"
            ),
            Array(
                "LOGIC" => "OR",
                Array(
                    'IBLOCK_ID'         => $iblockID,
                    'PROPERTY_FULL_URL' => $domain,
                    'ACTIVE'            => 'Y',
                ),
                Array(
                    'IBLOCK_ID'         => $iblockID,
                    'ACTIVE'            => 'Y',
                    'PROPERTY_SYNONYMS' => $domain,
                ),
            ),
            false,
            false,
            Array(
                'NAME',
                'CODE',
                'PROPERTY_FULL_URL',
                'PROPERTY_SYNONYMS'
            )
        );
        while($ob = $res->GetNextElement()){
            $arFields = $ob->GetFields();
            if ($arFields['PROPERTY_FULL_URL'] || $arFields["PROPERTY_FULL_URL_VALUE"]){
                if ($arFields['PROPERTY_FULL_URL_VALUE'] != $domain){
                    continue;
                }
                $_SESSION['VREGIONS_DEBUG']['DETECTED_REGION_BY_FULL_URL_PROP_ARFIELDS'] = $arFields;
                if ($arFields['CODE']){
                    return $arFields['CODE'];
                }
            } else{
                if ($arFields['PROPERTY_SYNONYMS'] || $arFields["PROPERTY_SYNONYMS_VALUE"]){
                    if (strpos($domain, $arFields['PROPERTY_SYNONYMS_VALUE']) === false){
                        continue;
                    }
                    $_SESSION['VREGIONS_DEBUG']['DETECTED_REGION_BY_SYNONYMS_PROP_ARFIELDS'] = $arFields;
                    if ($arFields['CODE']){
                        return $arFields['CODE'];
                    }
                }
            }
        }

        return false;
    }

    public static function setVregionsDefault(){
        if (!\CModule::IncludeModule("iblock")){
            return false;
        }
        if (\AristovVregionsHelper::isDemoEnd()){
            return false;
        }

        $res = \Bitrix\Main\Loader::includeSharewareModule("aristov.vregions");
        if ($res === 0 || $res === 3){
            return false;
        }

        // sobiraem dannye dlya regiona po umolchaniyu (chtoby nichego ne ekhalo iz-za krivorukosti zapolneniya i ot oshibok v sisteme)
        $IBLOCK_ID           = Tools::getModuleOption("vregions_iblock_id");
        $DEFAULT_REGION_CODE = Tools::getModuleOption("vregions_default");
        $useSesionCache      = (Tools::getModuleOption("vregions_use_session_cache") == "Y") ? 1 : 0;

        $_SESSION['VREGIONS_DEBUG']['DEFAULT_REGION_CODE'] = $DEFAULT_REGION_CODE;

        $regionDefault = Array();
        if (!is_array($_SESSION["VREGIONS_DEFAULT"]) || !$useSesionCache){
            $res = \CIBlockElement::GetList(
                Array(),
                Array(
                    "IBLOCK_ID" => $IBLOCK_ID,
                    "CODE"      => $DEFAULT_REGION_CODE
                ),
                false,
                false,
                Array()
            );
            if ($ob = $res->GetNextElement()){
                $arFieldsDefault = $ob->GetFields();
                $arPropsDefault  = $ob->GetProperties();
            } else{
                $_SESSION['VREGIONS_DEBUG']['DEFAULT_REGION_CODE_NOT_FOUND'] = 'Y';
                // если регион, который считался главным удалён - берём первый случайный регион в качестве главного
                $res = \CIBlockElement::GetList(
                    Array(
                        'ID' => 'ASC'
                    ),
                    Array(
                        "IBLOCK_ID" => $IBLOCK_ID,
                    ),
                    false,
                    false,
                    Array()
                );
                if ($ob = $res->GetNextElement()){
                    $arFieldsDefault = $ob->GetFields();
                    $arPropsDefault  = $ob->GetProperties();
                }
            }

            if (isset($arFieldsDefault) && is_array($arFieldsDefault)){
                $regionDefault["ID"]     = $arFieldsDefault["ID"];
                $regionDefault["NAME"]   = $arFieldsDefault["NAME"];
                $regionDefault["CODE"]   = $arFieldsDefault["CODE"];
                $regionDefault["ACTIVE"] = $arFieldsDefault["ACTIVE"];
                foreach ($arPropsDefault as $code => $array){
                    if (isset($array["VALUE"]["TEXT"])){
                        $regionDefault[$code] = $array["VALUE"]["TEXT"];
                    }
                    if ($array["VALUE"]){
                        $regionDefault[$code] = $array["VALUE"];
                    }
                }

                $regionDefault['URL'] = Tools::generateRegionLink($arFieldsDefault["CODE"], $arPropsDefault["HTTP_PROTOCOL"]["VALUE"], $arPropsDefault["FULL_URL"]["VALUE"], $arPropsDefault["WWW"]["VALUE"]);

                $_SESSION["VREGIONS_DEFAULT"] = $regionDefault;
            }
        } else{ // chtoby lishnij raz ne obrashchat'sya k bd
            $regionDefault = $_SESSION["VREGIONS_DEFAULT"];
        }

        return $regionDefault;
    }

    public static function setVregionsRegion($regionCode){
        if (!\CModule::IncludeModule("iblock")){
            return false;
        }
        if (\AristovVregionsHelper::isDemoEnd()){
            return false;
        }

        $res = \Bitrix\Main\Loader::includeSharewareModule("aristov.vregions");
        if ($res === 0 || $res === 3){
            return false;
        }

        $IBLOCK_ID         = Tools::getModuleOption("vregions_iblock_id");
        $useSesionCache    = (Tools::getModuleOption("vregions_use_session_cache") == "Y") ? 1 : 0;
        $workWithEmptyVars = (Tools::getModuleOption("vregions_work_with_empty_vars") == "Y") ? true : false;

        if ($regionCode != $_SESSION["VREGIONS_REGION"]["CODE"] || !$useSesionCache){ // esli nado sobirat' informaciyu pro region
            // sobiraem informaciyu pro region
            $region = Array();
            $res    = \CIBlockElement::GetList(Array(), Array(
                "IBLOCK_ID" => $IBLOCK_ID,
                "CODE"      => $regionCode,
                "ACTIVE"    => "Y"
            ),
                false,
                false,
                Array()
            );
            if ($ob = $res->GetNextElement()){
                $arFields        = $ob->GetFields();
                $arProps         = $ob->GetProperties();
                $region["ID"]    = $arFields["ID"];
                $region["NAME"]  = $arFields["NAME"];
                $region["~NAME"] = $arFields["~NAME"];
                $region["CODE"]  = $arFields["CODE"];
                foreach ($arProps as $code => $array){
                    if (isset($array["VALUE"]["TEXT"])){
                        $region[$code]     = $array["VALUE"]["TEXT"];
                        $region['~'.$code] = $array["~VALUE"]["TEXT"];
                    }
                    if ($array["VALUE"] || $workWithEmptyVars){
                        $region[$code]     = $array["VALUE"];
                        $region['~'.$code] = $array["~VALUE"];
                    }
                }

                $region['URL'] = Tools::generateRegionLink($arFields["CODE"], $arProps["HTTP_PROTOCOL"]["VALUE"], $arProps["FULL_URL"]["VALUE"], $arProps["WWW"]["VALUE"]);

                $_SESSION["VREGIONS_REGION"] = $region;

                return $region;
            } else{
                $ignoredDomains = explode(',', Tools::getModuleOption('subdomains_that_are_not_errors'));

                foreach ($ignoredDomains as $ignoredDomain){
                    if ($regionCode == trim($ignoredDomain)){
                        $_SESSION["VREGIONS_REGION"] = $_SESSION["VREGIONS_DEFAULT"];

                        return true;
                    }
                }

                return false; // esli net takogo goroda perevodim na osnovnoj host i pokazyvaem informaciyu defoltnogo regiona
            }
        } else{
            return $_SESSION["VREGIONS_REGION"];
        }
    }

    public static function setVregionsPhp(){
        if (\AristovVregionsHelper::isDemoEnd()){
            return false;
        }
        if (\Aristov\VRegions\Tools::getModuleOption("vregions_auto_geoposition_redirect_for_new") != "Y"){
            if (\Aristov\VRegions\Tools::getModuleOption("vregions_get_vregions_php_anyway") != "Y"){
                return false;
            }
        }

        // poluchaem gorod po ip (prost na vsyakiy) (plus nuzhno dlya ajax.php componenta)
        if (!isset($_SESSION["VREGIONS_PHP"])){
            $_SESSION["VREGIONS_PHP"] = Tools::getLocationByIP(Tools::getUserIP());
        }
    }

    public static function setLang(){
        if (\AristovVregionsHelper::isDemoEnd()){
            return false;
        }

        // rabota s yazykom
        // stavim nuzhnyj yazyk iz svojstva
        $langProp = Tools::getModuleOption("vregions_iblock_region_lang_prop");
        if ($_SESSION["VREGIONS_REGION"][$langProp]){
            if (strlen($_SESSION["VREGIONS_REGION"][$langProp]) == 2){ // na sluchaj ukazanija ne togo svojstva, naprimer, centr regiona
                Localization\Loc::setCurrentLang($_SESSION["VREGIONS_REGION"][$langProp]);
            }
        }
    }

    public static function setVregionsImLocation(){
        if (\AristovVregionsHelper::isDemoEnd()){
            return false;
        }

        if (!$_SESSION["VREGIONS_IM_LOCATION"]["SELECTED"]){
            if ($_SESSION["VREGIONS_IM_LOCATION"]["CITY_NAME"] != $_SESSION["VREGIONS_REGION"]["NAME"]){
                if (\CModule::IncludeModule("sale")){
                    // snazhala sopostovlyaem po imeni regiona
                    $db_vars = \CSaleLocation::GetList(
                        array(
                            "SORT" => "ASC",
                        ),
                        array(
                            'CITY_NAME_LANG' => $_SESSION["VREGIONS_REGION"]["NAME"]
                        ),
                        false,
                        false,
                        array()
                    );
                    if ($location = $db_vars->Fetch()){
                        $_SESSION["VREGIONS_IM_LOCATION"]                  = $location;
                        $_SESSION["VREGIONS_IM_LOCATION"]["LOCATION_CODE"] = \CSaleLocation::getLocationCODEbyID($_SESSION["VREGIONS_IM_LOCATION"]["ID"]);
                    } else{
                        // esli net locatii s takim gorodom, sravnivaem iz goroda pi ip
                        $db_vars = \CSaleLocation::GetList(
                            array(
                                "SORT" => "ASC",
                            ),
                            array(
                                'CITY_NAME_LANG' => $_SESSION["VREGIONS_PHP"]["city"]["name_ru"]
                            ),
                            false,
                            false,
                            array()
                        );
                        if ($location = $db_vars->Fetch()){
                            $_SESSION["VREGIONS_IM_LOCATION"]                  = $location;
                            $_SESSION["VREGIONS_IM_LOCATION"]["LOCATION_CODE"] = \CSaleLocation::getLocationCODEbyID($_SESSION["VREGIONS_IM_LOCATION"]["ID"]);
                        }
                    }
                }
            }
        }
    }

    public static function fireEvents(){
        if (\AristovVregionsHelper::isDemoEnd()){
            return false;
        }

        // zapusk storonnih sobytij
        foreach (GetModuleEvents(static::$MODULE_ID, "OnGenerateSessionArrays", true) as $arEvent){
            ExecuteModuleEventEx($arEvent);
        }
    }

    public static function redirectByHeaderString($link){
        if (\AristovVregionsHelper::isDemoEnd()){
            return false;
        }

        $httpCode = Tools::getModuleOption("vregions_redirect_http_code") ?: 301;
        LocalRedirect($link, false, $httpCode);
    }

    public static function rememberThereWasRedirect(){
        $_SESSION['VREGIONS_REGION_REDIRECTS_COUNT'] += 1;
    }

    public static function getRedirectsCount(){
        return intval($_SESSION['VREGIONS_REGION_REDIRECTS_COUNT']);
    }
}

class AristovVregionsHelper{

    static $MODULE_ID = "aristov.vregions";

    public static function isDemoEnd(){
        //        $res = CModule::IncludeModuleEx(static::$MODULE_ID);
        $res = \Bitrix\Main\Loader::includeSharewareModule(self::$MODULE_ID);

        return $res === MODULE_NOT_FOUND || $res === MODULE_DEMO_EXPIRED;
    }
}

class AristovVregionsHandlersHelper{

    public static $moduleID = "aristov.vregions";

    public static function onBeforeEventAddHandler(&$event, &$lid, &$arFields){
        if (\AristovVregionsHelper::isDemoEnd()){
            return false;
        }

        foreach ($_SESSION["VREGIONS_REGION"] as $code => $value){
            if (is_array($value) && $value['TEXT']){
                if ($value['TYPE'] && $value['TYPE'] == 'HTML'){
                    $value = html_entity_decode($value['TEXT']);
                } else{
                    $value = $value['TEXT'];
                }
            }

            $arFields["VREGION_".$code] = $value;
        }
    }

    public static function onEpilogHandler(){
        if (\AristovVregionsHelper::isDemoEnd()){
            return false;
        }

        if (!CSite::InDir('/bitrix/')){
            if (!defined('VREGIONS_DONT_WORK_WITH_META_ENDING') || VREGIONS_DONT_WORK_WITH_META_ENDING != 'Y'){
                // strochki v konze mety
                if ($title = Tools::getModuleOption('vregions_add_string_to_meta_title')){
                    Meta::addStringToTheEndOfTitle($title);
                    Meta::addStringToTheEndOfMetaProperty('title', $title);
                }
                if ($description = Tools::getModuleOption('vregions_add_string_to_meta_description')){
                    Meta::addStringToTheEndOfMetaProperty('description', $description);
                }
                if ($keywords = Tools::getModuleOption('vregions_add_string_to_meta_keywords')){
                    Meta::addStringToTheEndOfMetaProperty('keywords', $keywords);
                }
            }
        }

        // peremennye v mete
        \Aristov\VRegions\Tools::replaceVarsInMeta();
    }

    public static function onEndBufferContentHandler(&$content){
        if (\AristovVregionsHelper::isDemoEnd()){
            return false;
        }

        if (!defined('ADMIN_SECTION')){
            if (\Aristov\VRegions\Tools::getModuleOption('vregions_use_onendbuffercontent', 'N') == 'Y'){
                $content = \Aristov\Vregions\Tools::makeText($content);

                // text po ssylke
                $content = str_replace(
                    '#VREGION_TEXT_BY_URL#',
                    \Aristov\VRegions\Texts::getTextByUrl(),
                    $content
                );
            }

            // dop css
            if ($dopcss = Tools::getModuleOption('vregions_dop_css')){
                $content = str_replace(
                    '</head>',
                    '<style>'.$dopcss.'</style>
</head>',
                    $content
                );
            }
        }
    }

    public static function onGetOptimalPriceHandler($productID, $quantity = 1, $arUserGroups = array(), $renewal = "N", $arPrices = array(), $siteID = false, $arDiscountCoupons = false){
        if (\AristovVregionsHelper::isDemoEnd()){
            return true;
        }

        \CModule::IncludeModule('sale');
        \CModule::IncludeModule('iblock');

        $iblockID = (int) \CIBlockElement::GetIBlockByID($productID);;
        $currency            = \CCurrency::GetBaseCurrency();
        $pricesCode          = Array();
        $pricesResultsArrays = Array();

        $res = \Bitrix\Main\Loader::includeSharewareModule("aristov.vregions");
        if ($res === 0 || $res === 3){
            return true;
        }

        $propCode = \Aristov\VRegions\Tools::getModuleOption("vregions_iblock_region_price_code_prop");
        if (!$propCode){
            return true;
        }

        if (0){ // todo
            // работа со свойством наценки
            $pricesCode  = [];
            $dbPriceType = CCatalogGroup::GetList(
                array("SORT" => "ASC")
            );
            while($arPriceType = $dbPriceType->Fetch()){
                $pricesCode[] = $arPriceType['NAME'];
            }
        } else{
            if (!is_array($_SESSION["VREGIONS_REGION"][$propCode])){
                // старый способ
                if (!strlen($_SESSION["VREGIONS_REGION"][$propCode])){ // esli ne nuzhno, nechego zdes' delat'
                    return true;
                }
                $pricesCode[] = $_SESSION["VREGIONS_REGION"][$propCode] ? $_SESSION["VREGIONS_REGION"][$propCode] : 'BASE';
            } else{
                if (!strlen($_SESSION["VREGIONS_REGION"][$propCode][0])){ // esli ne nuzhno, nechego zdes' delat'
                    return true;
                }
                $pricesCode = $_SESSION["VREGIONS_REGION"][$propCode];
            }
        }

        foreach ($pricesCode as $priceCode){
            // poluchaem id zeny po kodu
            $arResultPrices = \CIBlockPriceTools::GetCatalogPrices($iblockID, Array($priceCode));
            $priceID        = $arResultPrices[$priceCode]["ID"];

            // poluchaem id zapisi o zene dlya dannogo tovara с учётом диапазонов
            $db_res = CPrice::GetList(
                ($by = "CATALOG_GROUP_ID"),
                ($order = "ASC"),
                array(
                    "PRODUCT_ID"       => $productID,
                    "CATALOG_GROUP_ID" => $priceID
                )
            );
            while($res = $db_res->Fetch()){
                if (!$res['QUANTITY_FROM'] || $res['QUANTITY_FROM'] <= $quantity){ // проверка ограничения по количеству снизу
                    if (!$res['QUANTITY_TO'] || $res['QUANTITY_TO'] >= $quantity){ // сверху
                        $prod_price = $res;
                        break;
                    }
                }
            }
            $realPrice = $prod_price["PRICE"];

            // skidka
            $arDiscounts = \CCatalogDiscount::GetDiscountByProduct(
                $productID,
                $arUserGroups,
                $renewal,
                Array($priceID),
                $siteID,
                $arDiscountCoupons
            );

            if (\Bitrix\Catalog\Product\Price\Calculation::isAllowedUseDiscounts()){ // если нужно применять скидку
                // uznaem skidku
                $discountPrice = \CCatalogProduct::CountPriceWithDiscount(
                    $prod_price["PRICE"],
                    $currency,
                    $arDiscounts
                );
            } else{
                $discountPrice = $prod_price["PRICE"];
            }

            // esli drugaya valyuta
            if ($prod_price["CURRENCY"] != $currency){
                $realPrice = \CCurrencyRates::ConvertCurrency($realPrice, $prod_price["CURRENCY"], $currency);
                $realPrice = roundEx($realPrice, 0);

                $discountPrice = \CCurrencyRates::ConvertCurrency($discountPrice, $prod_price["CURRENCY"], $currency);
                $discountPrice = roundEx($discountPrice, 0);
            }

            // nds
            $catalogProductArr = CCatalogProduct::GetByID($productID);
            $vatArr            = Array();
            if ($catalogProductArr['VAT_ID']){
                $vatRes = CCatalogVat::GetByID($catalogProductArr['VAT_ID']);
                $vatArr = $vatRes->Fetch();
                if ($vatArr['RATE'] && $catalogProductArr['VAT_INCLUDED'] == 'N'){
                    $discountPrice = $discountPrice * ((100 + $vatArr['RATE']) / 100);
                    $realPrice     = $realPrice * ((100 + $vatArr['RATE']) / 100);
                }
            }

            // okruglenie
            $unroundDiscountPrice = $discountPrice;
            $discountPrice        = \Bitrix\Catalog\Product\Price::roundPrice(
                $priceID,
                $discountPrice,
                $currency
            );
            $discountValue        = $realPrice - $discountPrice;

            $answer          = array();
            $answer['PRICE'] = array(
                'ID'                => $prod_price["ID"],
                'CATALOG_GROUP_ID'  => $priceID,
                'PRICE'             => $realPrice,
                'CURRENCY'          => $currency,
                'ELEMENT_IBLOCK_ID' => $iblockID,
                'VAT_RATE'          => $vatArr['RATE'],
                'VAT_INCLUDED'      => $catalogProductArr['VAT_INCLUDED'],
            );

            $answer['RESULT_PRICE'] = array(
                'BASE_PRICE'             => $realPrice,
                'DISCOUNT_PRICE'         => roundEx($discountPrice, 0),
                'UNROUND_DISCOUNT_PRICE' => $unroundDiscountPrice,
                'CURRENCY'               => $currency,
                'DISCOUNT'               => $discountValue ? $discountValue : 0,
                'PERCENT'                => $discountValue ? $discountValue / $realPrice * 100 : 0,
                'VAT_RATE'               => $vatArr['RATE'],
                'VAT_INCLUDED'           => $catalogProductArr['VAT_INCLUDED'],
            );

            $answer["DISCOUNT_PRICE"] = $discountPrice;
            $answer["PRODUCT_ID"]     = $productID;

            // так, потому что нужно обнулить ключи
            foreach ($arDiscounts as $arDiscount){
                $arDiscount['VALUE']            = $arDiscount['VALUE']; // здесь не округляем, так как некоторые модули для корзины могут начать неправильно себя вести
                $arDiscount['DISCOUNT_CONVERT'] = $arDiscount['VALUE'];
                $answer["DISCOUNT_LIST"][]      = $arDiscount;

                // запретить дальнейшее применение скидок
                if ($arDiscount['LAST_DISCOUNT'] == 'Y'){
                    break;
                }
            }

            if ($answer["DISCOUNT_LIST"][0]){
                $answer["DISCOUNT"] = $answer["DISCOUNT_LIST"][0];
            }

            $pricesResultsArrays[$discountPrice] = $answer;
        }

        // вытаскиваем наименьшую цену наверх, так как покупка происходит по наименьшей из цен
        ksort($pricesResultsArrays);

        return $pricesResultsArrays[key($pricesResultsArrays)];
    }
}

class VregionsAjaxHelper{

    public static function makeSubdomainFromRegionCode($regionCode, $defaultRegion){
        if ($regionCode == $defaultRegion){
            $subdomain = "";
        } else{
            $subdomain = $regionCode.'.';
        }

        return $subdomain;
    }

    public static function ifNeedToRedirect($ifNotAllowedRedirectToThisRegion, $rememberedRegionCode){
        $res = \Bitrix\Main\Loader::includeSharewareModule("aristov.vregions");
        if ($res === 0 || $res === 3){
            return false;
        }

        $permit_redirect_always = (\Aristov\VRegions\Tools::getModuleOption("vregions_auto_redirect") == 'Y' ? 1 : 0);
        $permit_redirect        = (\Aristov\VRegions\Tools::getModuleOption("vregions_auto_geoposition_redirect_for_new") == "Y") ? 1 : 0;
        $currentRegionCode      = $_SESSION['VREGIONS_REGION']['CODE'];

        if (!$ifNotAllowedRedirectToThisRegion){
            if ($permit_redirect){
                if (!$rememberedRegionCode){
                    return true;
                } else{
                    if ($permit_redirect_always && ($currentRegionCode != $rememberedRegionCode)){
                        return true;
                    }
                }
            }
        }
    }

    public static function setSessionArray($region){
        $_SESSION['VREGIONS_REGION'] = $region;
    }

    // todo переименовать в saveRegionCodeInCookie
    public static function setCookie($cookieValue){
        $res = \Bitrix\Main\Loader::includeSharewareModule("aristov.vregions");
        if ($res === 0 || $res === 3){
            return false;
        }

        global $APPLICATION;

        $domainInfo = \Aristov\Vregions\Tools::getCurrentSiteMainDomainInfo();
        $siteURL    = $domainInfo['domain_without_regions'];

        $cookieLifetime = intval(\Aristov\Vregions\Tools::getModuleOption("vregions_cookie_lifetime"));
        if (!$cookieLifetime){
            $cookieLifetime = 3600 * 24 * 30 * 2;
        }

        $application = \Bitrix\Main\Application::getInstance();
        $context     = $application->getContext();

        $cookie = new \Bitrix\Main\Web\Cookie("VREGION_SUBDOMAIN", $cookieValue, time() + $cookieLifetime);
        $cookie->setDomain(".".$siteURL);
        $cookie->setHttpOnly(false);
        $cookie->setSecure(false);
        $context->getResponse()->addCookie($cookie);

        if ($domainInfo['server_name']){
            // для регионов со своим адресом, например, site.spb.ru
            $cookie = new \Bitrix\Main\Web\Cookie("VREGION_SUBDOMAIN", $cookieValue, time() + $cookieLifetime);
            $cookie->setDomain($domainInfo['server_name']);
            $cookie->setHttpOnly(false);
            $cookie->setSecure(false);
            $context->getResponse()->addCookie($cookie);
        }

        //        $context->getResponse()->flush(""); // после обновления в октябре 2020 этот метод убивает весь вывод посла вызова

        //        $APPLICATION->set_cookie("VREGION_SUBDOMAIN", $cookie, time() + $cookieLifetime, "/", ".".$siteURL);

        $_SESSION['VREGION_SUBDOMAIN_COOKIE'] = $cookie;

        return true;
    }

    // todo переименовать в getRegionCodeFromCookie
    public static function getCookie(){
        $res = \Bitrix\Main\Loader::includeSharewareModule("aristov.vregions");
        if ($res === 0 || $res === 3){
            return false;
        }

        global $APPLICATION;

        $cookie = $_SESSION['VREGION_SUBDOMAIN_COOKIE'] ?: $APPLICATION->get_cookie("VREGION_SUBDOMAIN"); // чтобы не мучаться с настройками распространения куки на разных сайтах. Предотвращает повторный вопрос региона, если его выбрали, но перешли на поддомен

        if ($cookie instanceof Cookie){
            $cookie = $cookie->getValue(); // приводим к строке
        }

        return $cookie;
    }
}

Class AristovVregionsComponent{

    public static function getRegionsList($sortField1, $sortOrder1, $sortField2, $sortOrder2, $includePropsArray, $cacheTime){
        $VREGION_DEFAULT = \Aristov\VRegions\Tools::getModuleOption("vregions_default");
        $iblockID        = \Aristov\VRegions\Tools::getModuleOption("vregions_iblock_id");
        $domainInfo      = \Aristov\VRegions\Tools::getCurrentSiteMainDomainInfo();

        $items = [];

        $cache = \Bitrix\Main\Data\Cache::createInstance();
        if ($cache->initCache($cacheTime, "AristovVregionsComponent_getRegionsList".$iblockID.$VREGION_DEFAULT.$domainInfo['domain_without_regions'])){
            $items = $cache->getVars();
        } elseif ($cache->startDataCache()){
            $rs = \CIBlockElement::GetList(
                array(
                    $sortField1 => $sortOrder1,
                    $sortField2 => $sortOrder2,
                    "ID"        => "DESC",
                ),
                array(
                    "IBLOCK_ID" => $iblockID,
                    "ACTIVE"    => "Y"
                ),
                false,
                false,
                array(
                    "ID",
                    "IBLOCK_ID",
                    "NAME",
                    "CODE",
                    "PROPERTY_HTTP_PROTOCOL",
                    "PROPERTY_FULL_URL",
                    "PROPERTY_CHOSEN_ONE",
                    "PROPERTY_OBLAST",
                    "PROPERTY_WWW",
                    "PROPERTY_DO_NOT_SHOW_IN_COMPONENT",
                )
            );
            if ($includePropsArray){
                while($ob = $rs->GetNextElement()){
                    $arFields               = $ob->GetFields();
                    $arFields["PROPERTIES"] = $ob->GetProperties();

                    if ($arFields['PROPERTY_DO_NOT_SHOW_IN_COMPONENT_VALUE'] == 'Y'){
                        continue;
                    }

                    $arFields["HREF"] = Aristov\Vregions\Tools::generateRegionLink($arFields["CODE"], $arFields["PROPERTY_HTTP_PROTOCOL_VALUE"] ?: 'http', $arFields["PROPERTY_FULL_URL_VALUE"], $arFields["PROPERTY_WWW_VALUE"], $VREGION_DEFAULT, $domainInfo);

                    if ($arFields["CODE"] == $VREGION_DEFAULT){
                        $arResult["DEFAULT"] = $arFields;
                    }

                    if ($arFields["CODE"] == $VREGION_DEFAULT){
                        $arFields["CODE"] = "";
                    }

                    $arFields['CHOSEN_ONE'] = $arFields['PROPERTY_CHOSEN_ONE_VALUE'];
                    $arFields['OBLAST']     = $arFields['PROPERTY_OBLAST_VALUE'];

                    $items[$arFields['ID']] = $arFields;
                }
            } else{
                while($arFields = $rs->GetNext(true, true)){
                    if ($arFields['PROPERTY_DO_NOT_SHOW_IN_COMPONENT_VALUE'] == 'Y'){
                        continue;
                    }

                    $arFields["HREF"] = Aristov\Vregions\Tools::generateRegionLink($arFields["CODE"], $arFields["PROPERTY_HTTP_PROTOCOL_VALUE"] ?: 'http', $arFields["PROPERTY_FULL_URL_VALUE"], $arFields["PROPERTY_WWW_VALUE"], $VREGION_DEFAULT, $domainInfo);

                    if ($arFields["CODE"] == $VREGION_DEFAULT){
                        $arResult["DEFAULT"] = $arFields;
                    }

                    if ($arFields["CODE"] == $VREGION_DEFAULT){
                        $arFields["CODE"] = "";
                    }

                    $arFields['CHOSEN_ONE'] = $arFields['PROPERTY_CHOSEN_ONE_VALUE'];
                    $arFields['OBLAST']     = $arFields['PROPERTY_OBLAST_VALUE'];

                    $items[$arFields['ID']] = $arFields;
                }
            }

            $cache->endDataCache($items);
        }

        return $items;
    }

    public static function getCurrentPath(){
        return $_SERVER['REQUEST_URI'];
    }
}

?>
