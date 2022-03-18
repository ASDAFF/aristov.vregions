<?

namespace Aristov\Vregions\EventHandlers;

use Bitrix\Main\Localization;

\CModule::IncludeModule('aristov.vregions');

Localization\Loc::loadMessages(__FILE__);

class CvRegionsOnPageLoad{

    static $MODULE_ID = "aristov.vregions";

    public static function vRegionsMainHandler(){
        if (!class_exists('VRegionsPageLoadHelper')){
            return;
        }

        \CModule::IncludeModule("iblock");

        // na sluchaj esli kto-to pytaetsya otkryt' sajt po ip, to ne delaem nichego
        if (preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $_SERVER['SERVER_NAME'])){
            return;
        }

        $_SESSION['VREGIONS_DEBUG'] = Array();

        if (\VRegionsPageLoadHelper::isThereIsTooMuchSubdomains()){
            if (\VRegionsPageLoadHelper::getRedirectsCount() > \VRegionsPageLoadHelper::getMaxRedirectCount()){
                return false;
            }
            \VRegionsPageLoadHelper::rememberThereWasRedirect();
            \VRegionsPageLoadHelper::handleRegionDetectError();
        }

        $regionCode                                = \VRegionsPageLoadHelper::getRegionCodeOfCurrentDomain();
        $_SESSION['VREGIONS_DEBUG']['REGION_CODE'] = $regionCode;

        $phpRedirect = (\Aristov\VRegions\Tools::getModuleOption("vregions_php_redirect") == "Y") ? 1 : 0;
        if ($phpRedirect){
            if (\VRegionsPageLoadHelper::isNeedCookieRedirect($regionCode)){
                if (\VRegionsPageLoadHelper::getRedirectsCount() > \VRegionsPageLoadHelper::getMaxRedirectCount()){
                    return false;
                }
                \VRegionsPageLoadHelper::rememberThereWasRedirect();
                \VRegionsPageLoadHelper::redirectToRegionDomain(\VRegionsPageLoadHelper::getRegionCookie());
            }
        }

        \VRegionsPageLoadHelper::setVregionsDefault();

        if (!\VRegionsPageLoadHelper::setVregionsRegion($regionCode)){
            if ($_SESSION['VREGIONS_DEFAULT'] && $_SESSION['VREGIONS_DEFAULT']['ACTIVE'] != 'N'){ // защита от бесконечного редиректа при удалении всех регионов
                if (\VRegionsPageLoadHelper::getRedirectsCount() > \VRegionsPageLoadHelper::getMaxRedirectCount()){
                    return false;
                }
                \VRegionsPageLoadHelper::rememberThereWasRedirect();
                \VRegionsPageLoadHelper::handleRegionDetectError();
            }
        }

        \VRegionsPageLoadHelper::setVregionsPhp();

        \VRegionsPageLoadHelper::setLang();

        \VRegionsPageLoadHelper::setVregionsImLocation();

        \VRegionsPageLoadHelper::fireEvents();
    }

}

?>
