<?

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

Class aristov_vregions extends CModule{

    var $MODULE_ID = 'aristov.vregions';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    function __construct(){
        $arModuleVersion = array();
        include(__DIR__."/version.php");
        $this->MODULE_VERSION      = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME         = Loc::getMessage("ARISTOV_VREGIONS_MODULE_NAME");
        $this->MODULE_DESCRIPTION  = Loc::getMessage("ARISTOV_VREGIONS_MODULE_DESC");

        $this->PARTNER_NAME = getMessage("ARISTOV_VREGIONS_PARTNER_NAME");
        $this->PARTNER_URI  = getMessage("ARISTOV_VREGIONS_PARTNER_URI");

        $this->exclusionAdminFiles = array(
            '..',
            '.',
            'menu.php',
            'operation_description.php',
            'task_description.php'
        );
    }

    function InstallDB($arParams = array()){
        $this->createNecessaryIblocks();
    }

    function UnInstallDB($arParams = array()){
        \Bitrix\Main\Config\Option::delete($this->MODULE_ID);
        $this->deleteNecessaryIblocks();
    }

    function InstallEvents(){
        \Bitrix\Main\EventManager::getInstance()->registerEventHandler("main", "OnProlog", $this->MODULE_ID, '\Aristov\Vregions\EventHandlers\CvRegionsOnPageLoad', "vRegionsMainHandler");
        \Bitrix\Main\EventManager::getInstance()->registerEventHandler("main", "OnEpilog", $this->MODULE_ID, '\Aristov\Vregions\EventHandlers\OnEpilog', "handler");
        \Bitrix\Main\EventManager::getInstance()->registerEventHandler("catalog", "OnGetOptimalPrice", $this->MODULE_ID, '\Aristov\Vregions\EventHandlers\OnGetOptimalPriceHandler', "handler");
        \Bitrix\Main\EventManager::getInstance()->registerEventHandler("sale", "OnSaleComponentOrderProperties", $this->MODULE_ID, '\Aristov\Vregions\EventHandlers\OnSaleComponentOrderPropertiesHandler', "handler");
        \Bitrix\Main\EventManager::getInstance()->registerEventHandler("main", "OnEndBufferContent", $this->MODULE_ID, '\Aristov\Vregions\EventHandlers\OnEndBufferContentHandler', "handler");
        \Bitrix\Main\EventManager::getInstance()->registerEventHandler("main", "OnBeforeEventAdd", $this->MODULE_ID, '\Aristov\Vregions\EventHandlers\OnBeforeEventAddHandler', "handler");
        \Bitrix\Main\EventManager::getInstance()->registerEventHandler("sale", "OnSaleBasketItemBeforeSaved", $this->MODULE_ID, '\Aristov\Vregions\EventHandlers\OnSaleBasketItemBeforeSavedHandler', "handler");
    }

    function UnInstallEvents(){
        \Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler("main", "OnProlog", $this->MODULE_ID, '\Aristov\Vregions\EventHandlers\CvRegionsOnPageLoad', "vRegionsMainHandler");
        \Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler("main", "OnEpilog", $this->MODULE_ID, '\Aristov\Vregions\EventHandlers\OnEpilog', "handler");
        \Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler("catalog", "OnGetOptimalPrice", $this->MODULE_ID, '\Aristov\Vregions\EventHandlers\OnGetOptimalPriceHandler', "handler");
        \Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler("main", "OnEndBufferContent", $this->MODULE_ID, '\Aristov\Vregions\EventHandlers\OnEndBufferContentHandler', "handler");
        \Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler("main", "OnBeforeEventAdd", $this->MODULE_ID, '\Aristov\Vregions\EventHandlers\OnBeforeEventAddHandler', "handler");
        \Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler("sale", "OnSaleBasketItemBeforeSaved", $this->MODULE_ID, '\Aristov\Vregions\EventHandlers\OnSaleBasketItemBeforeSavedHandler', "handler");
    }

    function InstallFiles($arParams = array()){
        $path = $this->GetPath()."/install/components";

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path)){
            CopyDirFiles($path, $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
        }

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath().'/admin')){
            CopyDirFiles($this->GetPath()."/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
            if ($dir = opendir($path)){
                while(false !== $item = readdir($dir)){
                    if (in_array($item, $this->exclusionAdminFiles))
                        continue;
                    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$item,
                        '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/aristov.vregions/admin/'.$item.'");?'.'>');
                }
                closedir($dir);
            }
        }

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath().'/install/files')){
            $this->copyArbitraryFiles();
        }

        return true;
    }

    function UnInstallFiles(){
        \Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"].'/bitrix/components/'.$this->MODULE_ID.'/');

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath().'/admin')){
            DeleteDirFiles($_SERVER["DOCUMENT_ROOT"].$this->GetPath().'/install/admin/', $_SERVER["DOCUMENT_ROOT"].'/bitrix/admin');
            if ($dir = opendir($path)){
                while(false !== $item = readdir($dir)){
                    if (in_array($item, $this->exclusionAdminFiles))
                        continue;
                    \Bitrix\Main\IO\File::deleteFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$this->MODULE_ID.'_'.$item);
                }
                closedir($dir);
            }
        }

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath().'/install/files')){
            $this->deleteArbitraryFiles();
        }

        return true;
    }

    function copyArbitraryFiles(){
        $rootPath  = $_SERVER["DOCUMENT_ROOT"];
        $localPath = $this->GetPath().'/install/files';

        $dirIterator = new RecursiveDirectoryIterator($localPath, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator    = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $object){
            $destPath = $rootPath.DIRECTORY_SEPARATOR.$iterator->getSubPathName();
            ($object->isDir()) ? mkdir($destPath) : copy($object, $destPath);
        }
    }

    function deleteArbitraryFiles(){
        $rootPath  = $_SERVER["DOCUMENT_ROOT"];
        $localPath = $this->GetPath().'/install/files';

        $dirIterator = new RecursiveDirectoryIterator($localPath, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator    = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $object){
            if (!$object->isDir()){
                $file = str_replace($localPath, $rootPath, $object->getPathName());
                \Bitrix\Main\IO\File::deleteFile($file);
            }
        }
    }

    function createNecessaryIblocks(){
        $iblockType = $this->createIblockType();
        $iblockID   = $this->createIblock(
            Array(
                "IBLOCK_TYPE_ID"   => $iblockType,
                "ACTIVE"           => "Y",
                "LID"              => $this->getSitesIdsArray(),
                "VERSION"          => "1",
                "CODE"             => "vregions",
                "NAME"             => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_NAME"),
                "SORT"             => "500",
                "LIST_PAGE_URL"    => "#SITE_DIR#/vregions/index.php?ID=#IBLOCK_ID#",
                "SECTION_PAGE_URL" => "#SITE_DIR#/vregions/list.php?SECTION_ID=#SECTION_ID#",
                "DETAIL_PAGE_URL"  => "#SITE_DIR#/vregions/detail.php?ID=#ELEMENT_ID#",
                "INDEX_SECTION"    => "N",
                "INDEX_ELEMENT"    => "N",
                "FIELDS"           => Array(
                    "ACTIVE"                         => Array(
                        "DEFAULT_VALUE" => "Y",
                    ),
                    "PREVIEW_TEXT_TYPE"              => Array(
                        "DEFAULT_VALUE" => "text",
                    ),
                    "PREVIEW_TEXT_TYPE_ALLOW_CHANGE" => Array(
                        "DEFAULT_VALUE" => "N",
                    ),
                    "DETAIL_TEXT_TYPE"               => Array(
                        "DEFAULT_VALUE" => "text",
                    ),
                    "DETAIL_TEXT_TYPE_ALLOW_CHANGE"  => Array(
                        "DEFAULT_VALUE" => "N",
                    ),
                    "CODE"                           => Array(
                        "IS_REQUIRED"   => "Y",
                        "DEFAULT_VALUE" => Array(
                            "UNIQUE"          => "Y",
                            "TRANSLITERATION" => "Y",
                            "TRANS_CASE"      => "L",
                            "TRANS_SPACE"     => "-",
                            "TRANS_OTHER"     => "-",
                            "TRANS_EAT"       => "Y",
                        ),
                    ),
                ),
                "GROUP_ID"         => Array('2' => 'R'),
            )
        );
        // ustanovka infobloka v nastroikah modulya (todo k sozhaleniyu hardkod)
        COption::SetOptionString($this->MODULE_ID, 'vregions_iblock_id', $iblockID);

        $this->createIblockProp(
            Array(
                "IBLOCK_ID"     => $iblockID,
                "ACTIVE"        => "Y",
                "SORT"          => "100",
                "CODE"          => "WHERE",
                "NAME"          => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_PARAM_WHERE_NAME"),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE"     => "",
                "MULTIPLE"      => "N",
                "IS_REQUIRED"   => "N",
            )
        );
        $this->createIblockProp(
            Array(
                "IBLOCK_ID"     => $iblockID,
                "ACTIVE"        => "Y",
                "SORT"          => "200",
                "CODE"          => "DATELNYY_PADEG",
                "NAME"          => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_PARAM_DATELNYY_PADEG_NAME"),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE"     => "",
                "MULTIPLE"      => "N",
                "IS_REQUIRED"   => "N",
            )
        );
        $this->createIblockProp(
            Array(
                "IBLOCK_ID"     => $iblockID,
                "ACTIVE"        => "Y",
                "SORT"          => "500",
                "CODE"          => "TELEFON",
                "NAME"          => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_PARAM_TELEFON_NAME"),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE"     => "",
                "MULTIPLE"      => "N",
                "IS_REQUIRED"   => "N",
            )
        );
        $this->createIblockProp(
            Array(
                "IBLOCK_ID"     => $iblockID,
                "ACTIVE"        => "Y",
                "SORT"          => "500",
                "CODE"          => "ADRES",
                "NAME"          => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_PARAM_ADRES_NAME"),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE"     => "",
                "MULTIPLE"      => "N",
                "IS_REQUIRED"   => "N",
            )
        );
        $propCenter = $this->createIblockProp(
            Array(
                "IBLOCK_ID"     => $iblockID,
                "ACTIVE"        => "Y",
                "SORT"          => "2000",
                "CODE"          => "CENTR_REGIONA",
                "NAME"          => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_PARAM_CENTR_REGIONA_NAME"),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE"     => "map_google",
                "MULTIPLE"      => "N",
                "IS_REQUIRED"   => "N",
            )
        );

        $this->createIblockProp(
            Array(
                "IBLOCK_ID"     => $iblockID,
                "ACTIVE"        => "Y",
                "SORT"          => "500",
                "CODE"          => "CHOSEN_ONE",
                "NAME"          => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_PARAM_CHOSEN_ONE_NAME"),
                "PROPERTY_TYPE" => "L",
                "LIST_TYPE"     => "C",
                "USER_TYPE"     => "",
                "MULTIPLE"      => "N",
                "IS_REQUIRED"   => "N",
                "DEFAULT_VALUE" => "N",
                "VALUES"        => Array(
                    Array(
                        "VALUE" => "Y",
                        "DEF"   => "N",
                        "SORT"  => "100"
                    ),
                ),
            )
        );

        $this->createIblockProp(
            Array(
                "IBLOCK_ID"     => $iblockID,
                "ACTIVE"        => "Y",
                "SORT"          => "500",
                "CODE"          => "DO_NOT_SHOW_IN_COMPONENT",
                "NAME"          => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_PARAM_DO_NOT_SHOW_IN_COMPONENT_NAME"),
                "PROPERTY_TYPE" => "L",
                "LIST_TYPE"     => "C",
                "USER_TYPE"     => "",
                "MULTIPLE"      => "N",
                "IS_REQUIRED"   => "N",
                "DEFAULT_VALUE" => "N",
                "VALUES"        => Array(
                    Array(
                        "VALUE" => "Y",
                        "DEF"   => "N",
                        "SORT"  => "100"
                    ),
                ),
            )
        );

        $this->createIblockProp(
            Array(
                "IBLOCK_ID"     => $iblockID,
                "ACTIVE"        => "Y",
                "SORT"          => "500",
                "CODE"          => "HTTP_PROTOCOL",
                "NAME"          => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_PARAM_HTTP_PROTOCOL_NAME"),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE"     => "",
                "MULTIPLE"      => "N",
                "IS_REQUIRED"   => "N",
                "DEFAULT_VALUE" => "http",
            )
        );

        // ustanovka svoistva infobloka dlya centra v nastroikah modulya (todo k sozhaleniyu hardkod)
        COption::SetOptionString($this->MODULE_ID, 'vregions_iblock_region_centre_prop', 'CENTR_REGIONA');

        $element1ID = $this->createIblockElement(
            Array(
                "IBLOCK_ID"       => $iblockID,
                "ACTIVE"          => "Y",
                "SORT"            => "100",
                "CODE"            => "sankt-peterburg",
                "NAME"            => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_ELEMENT_SANKT-PETERBURG_NAME"),
                "PROPERTY_VALUES" => Array(
                    "WHERE"          => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_ELEMENT_SANKT-PETERBURG_PROP_WHERE_VALUE"),
                    "DATELNYY_PADEG" => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_ELEMENT_SANKT-PETERBURG_PROP_DATELNYY_PADEG_VALUE"),
                    "TELEFON"        => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_ELEMENT_SANKT-PETERBURG_PROP_TELEFON_VALUE"),
                    "ADRES"          => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_ELEMENT_SANKT-PETERBURG_PROP_ADRES_VALUE"),
                    "CENTR_REGIONA"  => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_ELEMENT_SANKT-PETERBURG_PROP_CENTR_REGIONA_VALUE"),
                    "HTTP_PROTOCOL"  => "http",
                ),
            )
        );
        // регион по умолчанию
        COption::SetOptionString($this->MODULE_ID, 'vregions_default', 'sankt-peterburg');

        $this->createIblockElement(
            Array(
                "IBLOCK_ID"       => $iblockID,
                "ACTIVE"          => "Y",
                "SORT"            => "500",
                "CODE"            => "moskva",
                "NAME"            => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_ELEMENT_MOSKVA_NAME"),
                "PROPERTY_VALUES" => Array(
                    "WHERE"          => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_ELEMENT_MOSKVA_PROP_WHERE_VALUE"),
                    "DATELNYY_PADEG" => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_ELEMENT_MOSKVA_PROP_DATELNYY_PADEG_VALUE"),
                    "TELEFON"        => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_ELEMENT_MOSKVA_PROP_TELEFON_VALUE"),
                    "ADRES"          => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_ELEMENT_MOSKVA_PROP_ADRES_VALUE"),
                    "CENTR_REGIONA"  => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_VREGIONS_ELEMENT_MOSKVA_PROP_CENTR_REGIONA_VALUE"),
                    "HTTP_PROTOCOL"  => "http",
                ),
            )
        );
    }

    function deleteNecessaryIblocks(){
        $this->removeIblockType();
    }

    function createIblockType(){
        global $DB, $APPLICATION;
        CModule::IncludeModule("iblock");

        $iblockType     = "aristov_vregions_iblock_type";
        $db_iblock_type = CIBlockType::GetList(Array("SORT" => "ASC"), Array("ID" => $iblockType));
        if (!$ar_iblock_type = $db_iblock_type->Fetch()){
            $arFieldsIBT = Array(
                'ID'       => $iblockType,
                'SECTIONS' => 'Y',
                'IN_RSS'   => 'N',
                'SORT'     => 500,
                'LANG'     => Array(
                    'en' => Array(
                        'NAME' => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_TYPE_NAME_EN"),
                    ),
                    'ru' => Array(
                        'NAME' => Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_TYPE_NAME_RU"),
                    )
                )
            );

            $obBlocktype = new CIBlockType;
            $DB->StartTransaction();
            $resIBT = $obBlocktype->Add($arFieldsIBT);
            if (!$resIBT){
                $DB->Rollback();
                $APPLICATION->ThrowException(Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_TYPE_ALREADY_EXISTS"));
            } else{
                $DB->Commit();

                return $iblockType;
            }
        }
    }

    function removeIblockType(){
        global $APPLICATION, $DB;
        CModule::IncludeModule("iblock");

        $iblockType = "aristov_vregions_iblock_type";

        $DB->StartTransaction();
        if (!CIBlockType::Delete($iblockType)){
            $DB->Rollback();
            $APPLICATION->ThrowException(Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_TYPE_DELETION_ERROR"));
        }
        $DB->Commit();
    }

    function createIblock($params){
        global $APPLICATION;
        CModule::IncludeModule("iblock");

        $ib = new CIBlock;

        $resIBE = CIBlock::GetList(Array(), Array(
            'TYPE'    => $params["IBLOCK_TYPE_ID"],
            'SITE_ID' => $params["SITE_ID"],
            "CODE"    => $params["CODE"]
        ));
        if ($ar_resIBE = $resIBE->Fetch()){
            $APPLICATION->ThrowException(Loc::getMessage("ARISTOV_VREGIONS_IBLOCK_ALREADY_EXISTS"));

            return false;
        } else{
            $ID = $ib->Add($params);

            return $ID;
        }

        return false;
    }

    function createIblockProp($arFieldsProp){
        CModule::IncludeModule("iblock");
        $ibp = new CIBlockProperty;

        return $ibp->Add($arFieldsProp);
    }

    function createIblockElement($arFields){
        CModule::IncludeModule("iblock");
        $el = new CIBlockElement;

        if ($PRODUCT_ID = $el->Add($arFields)){
            return $PRODUCT_ID;
        }

        return false;
    }

    function createNecessaryMailEvents(){
        return true;
    }

    function deleteNecessaryMailEvents(){
        return true;
    }

    function isVersionD7(){
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
    }

    function GetPath($notDocumentRoot = false){
        if ($notDocumentRoot){
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        } else{
            return dirname(__DIR__);
        }
    }

    function getSitesIdsArray(){
        $ids     = Array();
        $rsSites = CSite::GetList($by = "sort", $order = "desc");
        while($arSite = $rsSites->Fetch()){
            $ids[] = $arSite["LID"];
        }

        return $ids;
    }

    function touchModuleInstall(){
        $siteUrl = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];

        $url = 'https://av-promo.ru/api/module-install.php?site_url='.urldecode($siteUrl).'&module='.urldecode($this->MODULE_ID);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $page = curl_exec($ch);
        curl_close($ch);

        return $page;
    }

    function DoInstall(){

        global $APPLICATION;
        if ($this->isVersionD7()){
            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);

            $this->InstallDB();
            $this->createNecessaryMailEvents();
            $this->InstallEvents();
            $this->InstallFiles();
            $this->touchModuleInstall();

            // todo eto ne sdes dolzhno byt, a v options.php
            COption::SetOptionString($this->MODULE_ID, 'vregions_auto_geoposition_method', 'sxgeo');
            COption::SetOptionString($this->MODULE_ID, 'vregions_php_geoposition_tool', 'ipgeobase');
            COption::SetOptionString($this->MODULE_ID, 'vregions_redirect_http_code', '301');
            COption::SetOptionString($this->MODULE_ID, 'vregions_subdomain_level', '3');
            COption::SetOptionString($this->MODULE_ID, 'vregions_use_onendbuffercontent', 'Y');

            COption::SetOptionString($this->MODULE_ID, 'header_select_selector', 'body');
            COption::SetOptionString($this->MODULE_ID, 'header_select_selector_command', 'prepend');
            COption::SetOptionString($this->MODULE_ID, 'header_select_sort_by1', 'SORT');
            COption::SetOptionString($this->MODULE_ID, 'header_select_sort_order1', 'ASC');
            COption::SetOptionString($this->MODULE_ID, 'header_select_sort_by2', 'NAME');
            COption::SetOptionString($this->MODULE_ID, 'header_select_sort_order2', 'ASC');
            COption::SetOptionString($this->MODULE_ID, 'header_select_cache_time', '3600');
            COption::SetOptionString($this->MODULE_ID, 'header_select_show_popup_question', 'Y');
            COption::SetOptionString($this->MODULE_ID, 'header_select_popup_question_title', Loc::getMessage('ARISTOV_VREGIONS_HEADER_SELECT_POPUP_QUESTION_TITLE'));
            COption::SetOptionString($this->MODULE_ID, 'header_select_cols_count', '3');
            COption::SetOptionString($this->MODULE_ID, 'header_select_string_before_region_link', Loc::getMessage('ARISTOV_VREGIONS_HEADER_SELECT_STRING_BEFORE_REGION_LINK'));
            COption::SetOptionString($this->MODULE_ID, 'header_select_show_another_region_btn', 'Y');
        } else{
            $APPLICATION->ThrowException(Loc::getMessage("ARISTOV_VREGIONS_INSTALL_ERROR_VERSION"));
        }

        $APPLICATION->IncludeAdminFile(Loc::getMessage("ARISTOV_VREGIONS_INSTALL"), $this->GetPath()."/install/step.php");
    }

    function DoUninstall(){

        global $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        $this->UnInstallFiles();
        $this->deleteNecessaryMailEvents();
        $this->UnInstallEvents();

        if ($request["savedata"] != "Y")
            $this->UnInstallDB();

        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(Loc::getMessage("ARISTOV_VREGIONS_UNINSTALL"), $this->GetPath()."/install/unstep.php");
    }
}

?>