<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $USER, $APPLICATION, $DB;
$moduleID = 'aristov.vregions';
CModule::IncludeModule('aristov.vregions');
CModule::IncludeModule('iblock');

if ($_REQUEST['from'] && $_REQUEST['to']) {
    $siteID = $_REQUEST['site_id'];
    $siteAddress = $_REQUEST['site_address'];
    $ymlPath = $_REQUEST['from'];
    $outputDirPath = $_REQUEST['to'];

    // нормализация пути до исходного файла
    if (substr($ymlPath, 0, 1) !== '/') {
        $ymlPath = '/'.$ymlPath;
    }

    // нормализация пути до выходной папки
    if (substr($outputDirPath, -1, 1) != '/') {
        $outputDirPath = $outputDirPath.'/';
    }

    // запоминаем данные формы
    Aristov\VRegions\Tools::setModuleOption('yml_file_path_from', $ymlPath, $siteID);
    Aristov\VRegions\Tools::setModuleOption('yml_file_path_to', $outputDirPath, $siteID);
    Aristov\VRegions\Tools::setModuleOption('yml_site_id', $siteID, $siteID);
    Aristov\VRegions\Tools::setModuleOption('yml_site_address', $siteAddress, $siteID);

    // получаем xml один раз, а не в цикле
    $xml = simplexml_load_file($_SERVER['DOCUMENT_ROOT'].$ymlPath);

    $arSelect = Array(
        'ID',
        'IBLOCK_ID',
        'CODE',
    );
    // цена обрабатывается, только если нужно
    $pricePropCode = \Aristov\VRegions\Tools::getModuleOption("vregions_iblock_region_price_code_prop");
    if ($pricePropCode) {
        $arSelect[] = 'PROPERTY_'.$pricePropCode;
    }

    $resRegions = \CIBlockElement::GetList(
        Array(
            "SORT" => "ASC",
        ),
        Array(
            'IBLOCK_ID' => \Aristov\VRegions\Tools::getModuleOption("vregions_iblock_id"),
            'ACTIVE'    => 'Y',
            //            '!ID'=>$_SESSION['REGION_ID_WITH_CREATED_YML']
        ),
        false,
        false,
        $arSelect
    );
    while ($arFields = $resRegions->GetNext(true, false)) {
        $res = \Aristov\VRegions\YML::makeRegionalYml(
            $ymlPath,
            $outputDirPath,
            $arFields['CODE'],
            $arFields['PROPERTY_'.$pricePropCode.'_VALUE'],
            $siteID,
            $xml
        );
        if ($res['success']) {
            //            $_SESSION['REGION_ID_WITH_CREATED_YML'][] = $arFields['ID'];
            echo CAdminMessage::ShowMessage(
                array(
                    "TYPE"    => "OK",
                    "DETAILS" => Loc::getMessage("SUCCESS_MESSAGE", ['#FILE#' => $res['FILE_PATH']]),
                    "HTML"    => true,
                )
            );
        } else {
            echo CAdminMessage::ShowMessage(
                array(
                    "TYPE"    => "ERROR",
                    "DETAILS" => $res['message'],
                    "HTML"    => true,
                )
            );
        }
    }
}