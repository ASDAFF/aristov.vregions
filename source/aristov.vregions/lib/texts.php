<?php

namespace Aristov\VRegions;

use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class Texts{

    public static $moduleID = 'aristov.vregions';

    public static function getSectionText($sectionID){
        \CModule::IncludeModule('iblock');

        if ($sectionID){
            // ���� �� ������� ������������� �������� ���������?
            $res = \CIBlockElement::GetList(
                Array(
                    "SORT" => "ASC"
                ),
                Array(
                    'IBLOCK_TYPE'        => 'aristov_vregions_iblock_type',
                    'ACTIVE'             => 'Y',
                    'PROPERTY_CAT_ID'    => $sectionID,
                    'PROPERTY_REGION_ID' => $_SESSION['VREGIONS_REGION']['ID'],
                ),
                false,
                false,
                Array()
            );
            if ($ob = $res->GetNextElement()){
                $arFields = $ob->GetFields();

                // ���� � ����� �������� ���� ��������� �����, �� ���� ���
                if ($arFields["DETAIL_TEXT"]){
                    return html_entity_decode($arFields["DETAIL_TEXT"]);
                }
            }
        }

        return false;
    }

    public static function getElementText($elementID){
        \CModule::IncludeModule('iblock');

        if ($elementID){
            // ���� �� ������� ������������� �������� ���������?
            $res = \CIBlockElement::GetList(
                Array(
                    "SORT" => "ASC"
                ),
                Array(
                    'IBLOCK_TYPE'         => 'aristov_vregions_iblock_type',
                    'ACTIVE'              => 'Y',
                    'PROPERTY_ELEMENT_ID' => $elementID,
                    'PROPERTY_REGION_ID'  => $_SESSION['VREGIONS_REGION']['ID'],
                ),
                false,
                false,
                Array()
            );
            if ($ob = $res->GetNextElement()){
                $arFields = $ob->GetFields();

                // ���� � ����� �������� ���� ��������� �����, �� ���� ���
                if ($arFields["DETAIL_TEXT"]){
                    return html_entity_decode($arFields["DETAIL_TEXT"]);
                }
            }
        }

        return false;
    }

    public static function getTextByUrl($link = ''){
        \CModule::IncludeModule('iblock');

        if (!$link){
            $link = $_SERVER['REDIRECT_URL'] ?: $_SERVER['REQUEST_URI'];
            if (strpos($link, '?')){
                $link = substr($link, 0, strpos($link, '?')); // �������� ��� ���������
            }
        }

        if ($_SESSION['VREGIONS_REGION']["ID"]){
            // ���� �� ������� ������������� �������� ���������?
            $res = \CIBlockElement::GetList(
                Array(
                    "SORT" => "ASC"
                ),
                Array(
                    'IBLOCK_TYPE'        => 'aristov_vregions_iblock_type',
                    'ACTIVE'             => 'Y',
                    'PROPERTY_LINK'      => $link,
                    'PROPERTY_REGION_ID' => $_SESSION['VREGIONS_REGION']["ID"],
                ),
                false,
                false,
                Array(
                    'ID',
                    'IBLOCK_ID',
                    'DETAIL_TEXT',
                    'PROPERTY_LINK',
                )
            );
            if ($ob = $res->GetNextElement()){
                $arFields = $ob->GetFields();

                if (!$arFields['PROPERTY_LINK_VALUE']){
                    return false;
                }

                // ���� � ����� �������� ���� ��������� �����, �� ���� ���
                if ($arFields["DETAIL_TEXT"]){
                    return html_entity_decode($arFields["DETAIL_TEXT"]);
                }
            }
        }

        return false;
    }
}