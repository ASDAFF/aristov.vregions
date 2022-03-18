<? require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"); ?>
<? $APPLICATION->IncludeComponent(
    "vregions:header.select",
    "",
    Array(
        'POPUP_QUESTION_TITLE'           => \Aristov\VRegions\Tools::getModuleOption('header_select_popup_question_title'),
        'POPUP_QUESTION_YOUR_REGION_IS'  => \Aristov\VRegions\Tools::getModuleOption('header_select_popup_question_your_region_is'),
        'POPUP_QUESTION_YES_BUTTON_TEXT' => \Aristov\VRegions\Tools::getModuleOption('header_select_popup_question_yes_button_text'),
        'POPUP_QUESTION_NO_BUTTON_TEXT'  => \Aristov\VRegions\Tools::getModuleOption('header_select_popup_question_no_button_text'),
        'OBLAST_SELECT_NAME'             => \Aristov\VRegions\Tools::getModuleOption('header_select_oblast_select_name_text'),
        'ALLOW_OBLAST_FILTER'            => \Aristov\VRegions\Tools::getModuleOption('header_select_allow_oblast_filter'),
        'SHOW_OBLAST_LEFT'               => \Aristov\VRegions\Tools::getModuleOption('header_select_show_oblast_left'),
        'SHOW_CHOSEN_ITEMS_BLOCK'        => \Aristov\VRegions\Tools::getModuleOption('header_select_show_chosen_items_block'),
        'SHOW_LETTER_HEADINGS'           => \Aristov\VRegions\Tools::getModuleOption('header_select_show_letter_headings'),
    ),
    false
); ?>
<? require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php"); ?>