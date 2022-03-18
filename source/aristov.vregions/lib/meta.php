<?php

namespace Aristov\VRegions;

use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class Meta
{

    public static $moduleID = 'aristov.vregions';

    public static function addStringToTheEndOfMetaProperty($property, $string)
    {
        global $APPLICATION;

        $fromDir = false;

        $propertyValue = $APPLICATION->GetPageProperty($property);
        if (!$propertyValue) {
            $fromDir = true;
            $propertyValue = $APPLICATION->GetDirProperty($property);
        }

        if ($propertyValue) {
            $propertyValue .= $string;

            if ($fromDir) {
                $APPLICATION->SetDirProperty($property, $propertyValue);
            } else {
                $APPLICATION->SetPageProperty($property, $propertyValue);
            }
        }
    }

    public static function addStringToTheEndOfTitle($string)
    {
        global $APPLICATION;

        $title = $APPLICATION->GetTitle(false);

        if ($title) {
            $title .= $string;

            $APPLICATION->SetTitle($title);
        }
    }
}