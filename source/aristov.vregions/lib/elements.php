<?php

namespace Aristov\VRegions;

use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class Elements{

    public static $moduleID = 'aristov.vregions';

    public static function getReplaceArray($elementID){
        \CModule::IncludeModule('iblock');

        $replace = Array();

        $res = \CIBlockElement::GetList(
            Array(
                "SORT" => "ASC"
            ),
            Array(
                'ID'     => $elementID,
                'ACTIVE' => 'Y',
            ),
            false,
            false,
            Array(
                'ID',
                'IBLOCK_ID',
                'NAME',
                'CODE',
            )
        );
        if ($ob = $res->GetNextElement()){
            $arFields = $ob->GetFields();
            $arProps  = $ob->GetProperties();

            foreach ($arFields as $code => $value){
                $replace['#'.$code.'#'] = $value;
            }
            foreach ($arProps as $code => $value){
                $value = $value['VALUE'];
                if (is_array($value) && isset($value['TEXT'])){
                    $value = html_entity_decode($value['TEXT']);
                }
                $replace['#'.$code.'#'] = $value;
            }

            $priceArr           = \CCatalogProduct::GetOptimalPrice($arFields['ID']);
            $replace['#PRICE#'] = intval($priceArr['DISCOUNT_PRICE']);
        }

        return $replace;
    }

    public function getMetaFromThirdIblock($iblockID, $elementID){
        $title       = '';
        $description = '';

        $res = \CIBlockElement::GetList(
            Array(
                "SORT" => "ASC"
            ),
            Array(
                'IBLOCK_ID'        => $iblockID,
                'ACTIVE'           => 'Y',
                'PROPERTY_REGION'  => $_SESSION['VREGIONS_REGION']['ID'],
                'PROPERTY_ELEMENT' => $elementID,
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

            $replace = \Aristov\Vregions\Elements::getReplaceArray($elementID);

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