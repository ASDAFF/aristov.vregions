<?php

namespace Aristov\VRegions;

use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class Sections{

    public static $moduleID = 'aristov.vregions';

    public static function getReplaceArray($sectionID){
        \CModule::IncludeModule('iblock');

        $replace = Array();

        $dbSections = \CIBlockSection::GetList(
            Array(
                "SORT" => "ASC"
            ),
            Array(
                'ID'     => $sectionID,
                'ACTIVE' => 'Y',
            )
        );
        if ($arSection = $dbSections->GetNext()){
            foreach ($arSection as $code => $value){
                $replace['#'.$code.'#'] = $value;
            }
        }

        return $replace;
    }

    public function getMetaFromThirdIblock($iblockID, $sectionID){
        $title = '';
        $description = '';

        $res = \CIBlockElement::GetList(
            Array(
                "SORT" => "ASC"
            ),
            Array(
                'IBLOCK_ID'        => $iblockID,
                'ACTIVE'           => 'Y',
                'PROPERTY_REGION'  => $_SESSION['VREGIONS_REGION']['ID'],
                'PROPERTY_SECTION' => $sectionID,
            ),
            false,
            false,
            Array(
                'ID',
                'IBLOCK_ID',
                'NAME',
                'PROPERTY_TITLE',
                'PROPERTY_DESCRIPTION',
            )
        );
        if ($ob = $res->GetNextElement()){
            $arFields = $ob->GetFields();

            $replace = \Aristov\VRegions\Sections::getReplaceArray($sectionID);

            if ($arFields['PROPERTY_TITLE_VALUE']){
                $title = \Aristov\Vregions\Tools::makeText($arFields['PROPERTY_TITLE_VALUE'], $replace);
            }

            if ($arFields['PROPERTY_DESCRIPTION_VALUE']){
                $description = \Aristov\Vregions\Tools::makeText($arFields['PROPERTY_DESCRIPTION_VALUE'], $replace);
            }
        }

        return Array(
            'TITLE'       => $title,
            'DESCRIPTION' => $description,
        );
    }
}