<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

$colsCount = Array(
    '1' => 1,
    '2' => 2,
    '3' => 3,
    '4' => 4,
    '5' => 5,
    '6' => 6,
);

$arTemplateParameters = array(
    "SHOW_POPUP_QUESTION"            => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_SHOW_POPUP_QUESTION"),
        "TYPE"    => "CHECKBOX",
        "DEFAULT" => "Y"
    ),
    "POPUP_QUESTION_TITLE"           => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_POPUP_QUESTION_TITLE"),
        "TYPE"    => "TEXT",
        "DEFAULT" => GetMessage("VR_POPUP_QUESTION_TITLE_DEFAULT")
    ),
    "POPUP_QUESTION_YOUR_REGION_IS"  => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_POPUP_QUESTION_YOUR_REGION_IS"),
        "TYPE"    => "TEXT",
        "DEFAULT" => GetMessage("VR_POPUP_QUESTION_YOUR_REGION_IS_DEFAULT")
    ),
    "POPUP_QUESTION_YES_BUTTON_TEXT" => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_POPUP_QUESTION_YES_BUTTON_TEXT"),
        "TYPE"    => "TEXT",
        "DEFAULT" => GetMessage("VR_POPUP_QUESTION_YES_BUTTON_TEXT_DEFAULT")
    ),
    "POPUP_QUESTION_NO_BUTTON_TEXT"  => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_POPUP_QUESTION_NO_BUTTON_TEXT"),
        "TYPE"    => "TEXT",
        "DEFAULT" => GetMessage("VR_POPUP_QUESTION_NO_BUTTON_TEXT_DEFAULT")
    ),
    "COLS_COUNT"                     => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_COLS_COUNT"),
        "TYPE"    => "LIST",
        "VALUES"  => $colsCount,
        "DEFAULT" => 3
    ),
    "SHOW_SEARCH_FORM"               => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_SHOW_SEARCH_FORM"),
        "TYPE"    => "CHECKBOX",
        "DEFAULT" => "N"
    ),
    "STRING_BEFORE_REGION_LINK"      => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_STRING_BEFORE_REGION_LINK"),
        "TYPE"    => "TEXT",
        "DEFAULT" => GetMessage("VR_STRING_BEFORE_REGION_LINK_DEFAULT")
    ),
    "ALLOW_OBLAST_FILTER"            => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_ALLOW_OBLAST_FILTER"),
        "TYPE"    => "CHECKBOX",
        "DEFAULT" => "N"
    ),
    "SHOW_OBLAST_LEFT"               => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_SHOW_OBLAST_LEFT"),
        "TYPE"    => "CHECKBOX",
        "DEFAULT" => "N"
    ),
    "FIXED"                          => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_FIXED"),
        "TYPE"    => "CHECKBOX",
        "DEFAULT" => "N"
    ),
    "SHOW_ANOTHER_REGION_BTN"        => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_SHOW_ANOTHER_REGION_BTN"),
        "TYPE"    => "CHECKBOX",
        "DEFAULT" => "N"
    ),
    "OBLAST_SELECT_NAME"             => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_OBLAST_SELECT_NAME_LINK"),
        "TYPE"    => "TEXT",
        "DEFAULT" => GetMessage("VR_OBLAST_SELECT_NAME_DEFAULT")
    ),
    "HIDE_FROM_INDEX"                => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_HIDE_FROM_INDEX"),
        "TYPE"    => "CHECKBOX",
        "DEFAULT" => "N"
    ),
    "REGION_POPUP_TITLE"      => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_REGION_POPUP_TITLE"),
        "TYPE"    => "TEXT",
        "DEFAULT" => GetMessage("VR_REGION_POPUP_TITLE_DEFAULT")
    ),
    "FIND_REGION_TITLE"       => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_FIND_REGION_TITLE"),
        "TYPE"    => "TEXT",
        "DEFAULT" => GetMessage("VR_FIND_REGION_TITLE_DEFAULT")
    ),
    "SHOW_CHOSEN_ITEMS_BLOCK" => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_CHOSEN_ITEMS_BLOCK"),
        "TYPE"    => "CHECKBOX",
        "DEFAULT" => "N"
    ),
    "CHOSEN_ITEMS_TITLE"      => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_CHOSEN_ITEMS_TITLE"),
        "TYPE"    => "TEXT",
        "DEFAULT" => GetMessage("VR_CHOSEN_ITEMS_TITLE_DEFAULT")
    ),
    "OTHER_REGIONS_TITLE"     => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_OTHER_REGIONS_TITLE"),
        "TYPE"    => "TEXT",
        "DEFAULT" => GetMessage("VR_OTHER_REGIONS_TITLE_DEFAULT")
    ),
    "SHOW_LETTER_HEADINGS"    => Array(
        "PARENT"  => "ADDITIONAL_SETTINGS",
        "NAME"    => GetMessage("VR_SHOW_LETTER_HEADINGS"),
        "TYPE"    => "CHECKBOX",
        "DEFAULT" => "N"
    ),
//    "FIT_IN_SCREEN"           => Array(
//        "PARENT"  => "ADDITIONAL_SETTINGS",
//        "NAME"    => GetMessage("VR_FIT_IN_SCREEN"),
//        "TYPE"    => "CHECKBOX",
//        "DEFAULT" => "N"
//    ),
);
