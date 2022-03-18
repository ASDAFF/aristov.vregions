<?
// подключаем ланги
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
if (!class_exists('CMainPage')){
    include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/mainpage.php");
}
IncludeModuleLangFile(__FILE__);

CModule::IncludeModule("iblock");
CModule::IncludeModule('aristov.vregions');

$siteID = \CMainPage::GetSiteByHost();

global $APPLICATION;
?><?
$module_id = "aristov.vregions";

if (\AristovVregionsHelper::isDemoEnd()){
    echo CAdminMessage::ShowMessage(array(
        "TYPE"    => "ERROR",
        "DETAILS" => GetMessage("DEMO_END"),
        "HTML"    => true,
    ));
}

$RIGHT = $APPLICATION->GetGroupRight($module_id);
if ($RIGHT >= "R"){

    $arIBlocksSelect   = Array();
    $arIBlocksSelect[] = GetMessage("VREGIONS_SELECT");
    // собираем инфоблоки
    $rsIBlocks = CIBlock::GetList(array(
        'IBLOCK_TYPE' => 'ASC',
        'ID'          => 'ASC'
    ));
    while($arIBlock = $rsIBlocks->Fetch()){
        $arIBlockItem                     = $arIBlock; // просто если понадобитс¤ работать со свойствами перед выводом
        $arIBlocks[$arIBlock["ID"]]       = $arIBlockItem;
        $arIBlocksSelect[$arIBlock["ID"]] = $arIBlockItem["NAME"];
    }

    $IBLOCK_ID = 0;
    $IBLOCK_ID = COption::GetOptionString($module_id, "vregions_iblock_id", '', $siteID);

    $arVars   = Array(); // дл¤ вкладки переменных
    $arVars[] = Array(
        "CODE" => "#VREGION_NAME#",
        "PHP"  => '$_SESSION["VREGIONS_REGION"]["NAME"]',
        "DESC" => GetMessage("NAME")
    );
    $arVars[] = Array(
        "CODE" => "#VREGION_CODE#",
        "PHP"  => '$_SESSION["VREGIONS_REGION"]["CODE"]',
        "DESC" => GetMessage("CODE")
    );

    // todo а может добавл¤ть эти два?
    // $arVars[] = Array(
    // "CODE" => "#PREVIEW_TEXT#",
    // "PHP" => '$_SESSION["VREGIONS_REGION"]["PREVIEW_TEXT"]',
    // "DESC" => GetMessage("PREVIEW_TEXT")
    // );
    // $arVars[] = Array(
    // "CODE" => "#DETAIL_TEXT#",
    // "PHP" => '$_SESSION["VREGIONS_REGION"]["DETAIL_TEXT"]',
    // "DESC" => GetMessage("DETAIL_TEXT")
    // );

    // собираем свойства
    $arPropsSelect   = Array();
    $arPropsSelect[] = GetMessage("VREGIONS_SELECT");
    // чтобы не показывать сотни свойств, лучше вообще не будем показывать, если не выбран инфоблок
    if ($IBLOCK_ID){
        $properties = CIBlockProperty::GetList(Array(
            "SORT" => "ASC",
            "NAME" => "ASC"
        ), Array(
            "ACTIVE"    => "Y",
            "IBLOCK_ID" => $IBLOCK_ID
        ));
        while($prop_fields = $properties->GetNext()){
            $arPropsSelect[$prop_fields["CODE"]] = $prop_fields["NAME"];
            $arVars[]                            = Array(
                "CODE" => "#VREGION_".$prop_fields["CODE"]."#",
                "PHP"  => '$_SESSION["VREGIONS_REGION"]["'.$prop_fields["CODE"].'"]',
                "DESC" => $prop_fields["NAME"]
            );
        }
    }

    // собираем элементы
    $arItemsSelect   = Array();
    $arItemsSelect[] = GetMessage("VREGIONS_SELECT");
    // чтобы не показывать сотни элементов, лучше вообще не будем показывать, если не выбран инфоблок
    if ($IBLOCK_ID){
        $rsMR = CIBlockElement::GetList(Array(
            "SORT" => "ASC",
            "NAME" => "ASC"
        ), Array(
            "ACTIVE"    => "Y",
            "IBLOCK_ID" => $IBLOCK_ID
        ), false, false, Array(
            "ID",
            "CODE",
            "NAME"
        ));
        while($obMR = $rsMR->GetNextElement()){
            $arFieldsMR                         = $obMR->GetFields();
            $arItemsSelect[$arFieldsMR["CODE"]] = $arFieldsMR["NAME"];
        }
    }
    // vprint($arItemsSelect);

    // способы определения геопозиции
    $vregions_auto_geoposition_methods           = Array();
    $vregions_auto_geoposition_methods[]         = GetMessage("VREGIONS_SELECT");
    $vregions_auto_geoposition_methods["google"] = GetMessage("VREGIONS_GOOGLE_HTML5_METHOD");
    $vregions_auto_geoposition_methods["sxgeo"]  = GetMessage("VREGIONS_SXGEO_METHOD");

    // biblioteki dlya php
    $vregions_php_geoposition_tools              = Array();
    $vregions_php_geoposition_tools[]            = GetMessage("VREGIONS_SELECT");
    $vregions_php_geoposition_tools["sxgeo"]     = GetMessage("VREGIONS_PHP_GEOPOSITION_TOOL_SXGEO");
    $vregions_php_geoposition_tools["ipgeobase"] = GetMessage("VREGIONS_PHP_GEOPOSITION_TOOL_IPGEOBASE");

    // сортировки
    $arSorts = Array(
        ""     => GetMessage("VREGIONS_SELECT"),
        "ASC"  => GetMessage("T_IBLOCK_DESC_ASC"),
        "DESC" => GetMessage("T_IBLOCK_DESC_DESC"),
    );

    $arSortFields = Array(
        ""            => GetMessage("VREGIONS_SELECT"),
        "ID"          => GetMessage("T_IBLOCK_DESC_FID"),
        "NAME"        => GetMessage("T_IBLOCK_DESC_FNAME"),
        "ACTIVE_FROM" => GetMessage("T_IBLOCK_DESC_FACT"),
        "SORT"        => GetMessage("T_IBLOCK_DESC_FSORT"),
        "TIMESTAMP_X" => GetMessage("T_IBLOCK_DESC_FTSAMP")
    );

    $bVarsFromForm = false; // переменна¤ флаг: пришли ли данные с формы ## todo разобратьс¤ когда ставить true
    // массив вкладок, свойств
    $aTabs      = Array(
        Array(
            "DIV"     => "index",
            "TAB"     => GetMessage("VREGIONS_OPTIONS_TAB_INDEX"),
            "ICON"    => "vregions_settings",
            "TITLE"   => GetMessage("VREGIONS_OPTIONS_TAB_INDEX_TITLE"),
            "OPTIONS" => Array(
                // "vregions_site_url" => Array(GetMessage("VREGIONS_SITE_URL"), Array("text")), // todo перезагрузка при обновлении
                "vregions_iblock_id"                             => Array(
                    GetMessage("VREGIONS_OPTIONS_IBLOCK"),
                    Array(
                        "select",
                        $arIBlocksSelect
                    )
                ),
                // todo перезагрузка при обновлении
                "vregions_default"                               => Array(
                    GetMessage("VREGIONS_DEFAULT"),
                    Array(
                        "select",
                        $arItemsSelect
                    )
                ),
                "vregions_error_page"                            => Array(
                    GetMessage("VREGIONS_ERROR_PAGE"),
                    Array("text")
                ),
                "vregions_error_handle"                          => Array(
                    GetMessage("VREGIONS_ERROR_HANDLE"),
                    Array(
                        "checkbox",
                        "N"
                    )
                ),
                "vregions_redirect_http_code"                    => Array(
                    GetMessage("VREGIONS_VREGIONS_REDIRECT_HTTP_CODE"),
                    Array("text"),
                    "",
                    "",
                    ""
                ),
                "vregions_auto_geoposition_method"               => Array(
                    GetMessage("VREGIONS_AUTO_GEOPOSITION_METHOD"),
                    Array(
                        "select",
                        $vregions_auto_geoposition_methods
                    )
                ),
                "vregions_php_geoposition_tool"                  => Array(
                    GetMessage("VREGIONS_PHP_GEOPOSITION_TOOL"),
                    Array(
                        "select",
                        $vregions_php_geoposition_tools
                    )
                ),
                "vregions_auto_geoposition_redirect_for_new"     => Array(
                    GetMessage("VREGIONS_AUTO_GEOPOSITION_REDIRECT"),
                    Array(
                        "checkbox",
                        "N"
                    ),
                    "",
                    GetMessage("VREGIONS_AUTO_GEOPOSITION_REDIRECT_FOR_NEW_DESCR")
                ),
                // todo имеет смысл только при отмеченном vregions_auto_geoposition
                "vregions_auto_redirect"                         => Array(
                    GetMessage("VREGIONS_AUTO_REDIRECT"),
                    Array(
                        "checkbox",
                        "N"
                    ),
                    "",
                    ""
                ),
                "vregions_auto_redirect_only_main"               => Array(
                    GetMessage("VREGIONS_AUTO_REDIRECT_ONLY_MAIN"),
                    Array(
                        "checkbox",
                        "N"
                    ),
                    "",
                    ""
                ),
                "vregions_iblock_region_centre_prop"             => Array(
                    GetMessage("VREGIONS_IBLOCK_REGION_CENTRE_PROP"),
                    Array(
                        "select",
                        $arPropsSelect
                    )
                ),
                // todo перезагрузка при обновлении
                "vregions_iblock_region_lang_prop"               => Array(
                    GetMessage("VREGIONS_IBLOCK_REGION_LANG_PROP"),
                    Array(
                        "select",
                        $arPropsSelect
                    )
                ),
                // todo перезагрузка при обновлении
                "vregions_iblock_region_price_code_prop"         => Array(
                    GetMessage("VREGIONS_IBLOCK_REGION_PRICE_CODE_PROP"),
                    Array(
                        "select",
                        $arPropsSelect
                    )
                ),
                //                "vregions_iblock_region_extra_charge_prop"         => Array(
                //                    GetMessage("VREGIONS_IBLOCK_REGION_EXTRA_CHARGE_PROP"),
                //                    Array(
                //                        "select",
                //                        $arPropsSelect
                //                    )
                //                ),
                "vregions_subdomain_level"                       => Array(
                    GetMessage("VREGIONS_SUBDOMAIN_LEVEL"),
                    Array("text"),
                    "",
                    "",
                    ""
                ),
                "vregions_use_session_cache"                     => Array(
                    GetMessage("VREGIONS_USE_SESSION_CACHE"),
                    Array(
                        "checkbox",
                        "Y"
                    )
                ),
                "vregions_cookie_lifetime"                       => Array(
                    GetMessage("VREGIONS_COOKIE_LIFETIME"),
                    Array("text"),
                    "",
                    GetMessage("VREGIONS_COOKIE_LIFETIME_DESCR")
                ),
                "vregions_use_onendbuffercontent"                => Array(
                    GetMessage("VREGIONS_USE_ONENDBUFFERCONTENT"),
                    Array(
                        "checkbox",
                        "N"
                    ),
                    "",
                    GetMessage("VREGIONS_USE_ONENDBUFFERCONTENT_DESCR")
                ),
                "vregions_work_with_empty_vars"                  => Array(
                    GetMessage("VREGIONS_WORK_WITH_EMPTY_VARS"),
                    Array(
                        "checkbox",
                        "N"
                    ),
                    "",
                    GetMessage("VREGIONS_WORK_WITH_EMPTY_VARS_DESCR")
                ),
                "vregions_php_dont_substitute_location_at_order" => Array(
                    GetMessage("VREGIONS_PHP_DONT_SUBSTITUTE_LOCATION_AT_ORDER"),
                    Array(
                        "checkbox",
                        "N"
                    ),
                    "",
                    GetMessage("VREGIONS_PHP_DONT_SUBSTITUTE_LOCATION_AT_ORDER_DESCR")
                ),
                "vregions_work_on_one_domain"                    => Array(
                    GetMessage("VREGIONS_VREGIONS_WORK_ON_ONE_DOMAIN"),
                    Array(
                        "checkbox",
                        "N"
                    ),
                    "",
                    GetMessage("VREGIONS_VREGIONS_WORK_ON_ONE_DOMAIN_DESCR")
                ),
                "subdomains_that_are_not_errors"                   => Array(
                    GetMessage("SUBDOMAINS_THAT_ARE_NOT_ERRORS"),
                    Array(
                        "text"
                    ),
                    "",
                    GetMessage("SUBDOMAINS_THAT_ARE_NOT_ERRORS_DESCR")
                ),
                "dont_show_ask_window_if_already_on_needed_region" => Array(
                    GetMessage("DONT_SHOW_ASK_WINDOW_IF_ALREADY_ON_NEEDED_REGION"),
                    Array(
                        "checkbox",
                    ),
                    "",
                    GetMessage("DONT_SHOW_ASK_WINDOW_IF_ALREADY_ON_NEEDED_REGION_DESCR")
                ),
                "vregions_add_region_code_prop_to_basket"        => Array(
                    GetMessage("VREGIONS_ADD_REGION_CODE_PROP_TO_BASKET"),
                    Array(
                        "checkbox",
                    ),
                    "",
                    GetMessage("VREGIONS_ADD_REGION_CODE_PROP_TO_BASKET_DESCR")
                )
            )
        ),

        Array(
            "DIV"     => "component",
            "TAB"     => GetMessage("VREGIONS_OPTIONS_TAB_COMPONENT"),
            "ICON"    => "vregions_settings",
            "TITLE"   => GetMessage("VREGIONS_OPTIONS_TAB_COMPONENT_TITLE"),
            "OPTIONS" => Array(
                "vregions_post_component_on_pages"             => Array(
                    GetMessage("VREGIONS_POST_COMPONENT_ON_PAGES"),
                    Array(
                        "checkbox",
                        "N"
                    ),
                    "",
                    GetMessage("VREGIONS_POST_COMPONENT_ON_PAGES_DESCR")
                ),
                "header_select_selector"                       => Array(
                    GetMessage("HEADER_SELECT_SELECTOR"),
                    Array("text"),
                    "",
                    "",
                    ""
                ),
                "header_select_selector_command"               => Array(
                    GetMessage("HEADER_SELECT_SELECTOR_COMMAND"),
                    Array("text"),
                    "",
                    "",
                    ""
                ),
                "header_select_sort_by1"                       => Array(
                    GetMessage("HEADER_SELECT_SORT_BY1"),
                    Array(
                        "select",
                        $arSortFields
                    ),
                    "",
                    "",
                    ""
                ),
                "header_select_sort_order1"                    => Array(
                    GetMessage("HEADER_SELECT_SORT_ORDER1"),
                    Array(
                        "select",
                        $arSorts
                    ),
                    "",
                    "",
                    ""
                ),
                "header_select_sort_by2"                       => Array(
                    GetMessage("HEADER_SELECT_SORT_BY2"),
                    Array(
                        "select",
                        $arSortFields
                    ),
                    "",
                    "",
                    ""
                ),
                "header_select_sort_order2"                    => Array(
                    GetMessage("HEADER_SELECT_SORT_ORDER2"),
                    Array(
                        "select",
                        $arSorts
                    ),
                    "",
                    "",
                    ""
                ),
                "header_select_cache_time"                     => Array(
                    GetMessage("HEADER_SELECT_CACHE_TIME"),
                    Array("text"),
                    "",
                    "",
                    ""
                ),
                "header_select_show_popup_question"            => Array(
                    GetMessage("HEADER_SELECT_SHOW_POPUP_QUESTION"),
                    Array(
                        "checkbox",
                        "N"
                    ),
                    "",
                    "",
                    ""
                ),
                "header_select_popup_question_title"           => Array(
                    GetMessage("HEADER_SELECT_POPUP_QUESTION_TITLE"),
                    Array("text"),
                    "",
                    "",
                    ""
                ),
                "header_select_popup_question_your_region_is"  => Array(
                    GetMessage("HEADER_SELECT_POPUP_QUESTION_YOUR_REGION_IS"),
                    Array("text"),
                    "",
                    "",
                    ""
                ),
                "header_select_popup_question_yes_button_text" => Array(
                    GetMessage("HEADER_SELECT_POPUP_QUESTION_YES_BUTTON_TEXT"),
                    Array("text"),
                    "",
                    "",
                    ""
                ),
                "header_select_popup_question_no_button_text"  => Array(
                    GetMessage("HEADER_SELECT_POPUP_QUESTION_NO_BUTTON_TEXT"),
                    Array("text"),
                    "",
                    "",
                    ""
                ),
                "header_select_cols_count"                     => Array(
                    GetMessage("HEADER_SELECT_COLS_COUNT"),
                    Array("text"),
                    "",
                    "",
                    ""
                ),
                "header_select_show_search_form"               => Array(
                    GetMessage("HEADER_SELECT_SHOW_SEARCH_FORM"),
                    Array(
                        "checkbox",
                        "N"
                    ),
                    "",
                    "",
                    ""
                ),
                "header_select_string_before_region_link"      => Array(
                    GetMessage("HEADER_SELECT_STRING_BEFORE_REGION_LINK"),
                    Array("text"),
                    "",
                    "",
                    ""
                ),
                "header_select_allow_oblast_filter"            => Array(
                    GetMessage("HEADER_SELECT_ALLOW_OBLAST_FILTER"),
                    Array(
                        "checkbox",
                        "N"
                    ),
                    "",
                    GetMessage("HEADER_SELECT_ALLOW_OBLAST_FILTER_DESCR")
                ),
                "header_select_oblast_select_name_text"        => Array(
                    GetMessage("HEADER_SELECT_OBLAST_SELECT_NAME_TEXT"),
                    Array(
                        "text",
                    ),
                    "",
                    GetMessage("HEADER_SELECT_OBLAST_SELECT_NAME_TEXT_DESCR")
                ),
                "header_select_show_oblast_left"               => Array(
                    GetMessage("HEADER_SELECT_SHOW_OBLAST_LEFT"),
                    Array(
                        "checkbox",
                        "N"
                    ),
                    "",
                    GetMessage("HEADER_SELECT_OBLAST_SELECT_NAME_TEXT_DESCR")
                ),
                "header_select_fixed"                          => Array(
                    GetMessage("HEADER_SELECT_FIXED"),
                    Array(
                        "checkbox",
                        "N"
                    ),
                    "",
                    "",
                    ""
                ),
                "header_select_show_another_region_btn"        => Array(
                    GetMessage("HEADER_SELECT_SHOW_ANOTHER_REGION_BTN"),
                    Array(
                        "checkbox",
                        "N"
                    ),
                    "",
                    "",
                    ""
                ),
                "header_select_include_props_array"            => Array(
                    GetMessage("HEADER_SELECT_INCLUDE_PROPS_ARRAY"),
                    Array(
                        "checkbox",
                        "N"
                    ),
                    "",
                    "",
                    ""
                ),
                "header_select_show_chosen_items_block"        => Array(
                    GetMessage("HEADER_SELECT_SHOW_CHOSEN_ITEMS_BLOCK"),
                    Array(
                        "checkbox",
                        "N"
                    ),
                    "",
                    "",
                    ""
                ),
                "header_select_show_letter_headings"           => Array(
                    GetMessage("HEADER_SELECT_SHOW_SHOW_LETTER_HEADINGS"),
                    Array(
                        "checkbox",
                        "N"
                    ),
                    "",
                    "",
                    ""
                ),
                "vregions_dop_css"                             => Array(
                    GetMessage("VREGIONS_DOP_CSS"),
                    Array("textarea"),
                    "",
                    "",
                    ""
                ),
            )
        ),

        Array(
            "DIV"     => "seo",
            "TAB"     => GetMessage("VREGIONS_OPTIONS_TAB_SEO"),
            "ICON"    => "vregions_settings",
            "TITLE"   => GetMessage("VREGIONS_OPTIONS_TAB_SEO_TITLE"),
            "OPTIONS" => Array(
                "vregions_add_string_to_meta_title"       => Array(
                    GetMessage("VREGIONS_ADD_STRING_TO_META_TITLE"),
                    Array(
                        "text"
                    ),
                    "",
                    GetMessage("VREGIONS_ADD_STRING_TO_META_TITLE__DESCR")
                ),
                "vregions_add_string_to_meta_description" => Array(
                    GetMessage("VREGIONS_ADD_STRING_TO_META_DESCRIPTION"),
                    Array(
                        "text"
                    ),
                    "",
                    GetMessage("VREGIONS_ADD_STRING_TO_META_DESCRIPTION__DESCR")
                ),
                "vregions_add_string_to_meta_keywords"    => Array(
                    GetMessage("VREGIONS_ADD_STRING_TO_META_KEYWORDS"),
                    Array(
                        "text"
                    ),
                    "",
                    GetMessage("VREGIONS_ADD_STRING_TO_META_KEYWORDS__DESCR")
                ),
            )
        ),

        Array(
            "DIV"     => "variables",
            "TAB"     => GetMessage("VREGIONS_OPTIONS_TAB_VARIABLES"),
            "ICON"    => "vregions_settings",
            "TITLE"   => GetMessage("VREGIONS_OPTIONS_TAB_VARIABLES_TITLE"),
            "OPTIONS" => Array()
        ),
        array(
            "DIV"     => "rights",
            "TAB"     => GetMessage("MAIN_TAB_RIGHTS"),
            "ICON"    => "vregions_settings",
            "TITLE"   => GetMessage("MAIN_TAB_TITLE_RIGHTS"),
            "OPTIONS" => Array()
        )
    );
    $tabControl = new CAdminTabControl("tabControl", $aTabs);

    if ($REQUEST_METHOD == "POST" && strlen($Update.$Apply.$RestoreDefaults) > 0 && check_bitrix_sessid()){
        if (strlen($RestoreDefaults) > 0) // если было выбрано "по умолчанию", то сбрасывает все option'ы
            COption::RemoveOption($module_id, '', $siteID);
        else{
            if (!$bVarsFromForm){
                // обработка формы
                foreach ($aTabs as $i => $aTab){
                    foreach ($aTab["OPTIONS"] as $name => $arOption){
                        $disabled = array_key_exists("disabled", $arOption) ? $arOption["disabled"] : "";
                        if ($disabled)
                            continue;

                        $val = $_POST[$name];
                        if ($arOption[1][0] == "checkbox" && $val != "Y")
                            $val = "N";

                        COption::SetOptionString($module_id, $name, $val, $arOption[0], $siteID);
                        // todo очистка кеша
                    }
                }
            }
        }

        ob_start();
        $Update = $Update.$Apply;
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
        ob_end_clean();
    }
    $tabControl->Begin();
    ?>
	<form method="post"
	      action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>"
	      id="options">
        <?
        foreach ($aTabs as $caTab => $aTab){
            $tabControl->BeginNextTab();
            if ($aTab["DIV"] != "rights" && $aTab["DIV"] != "variables"){ // не особа¤ вкладка
                foreach ($aTab["OPTIONS"] as $name => $arOption){
                    if ($bVarsFromForm)
                        $val = $_POST[$name];
                    else
                        $val = COption::GetOptionString($module_id, $name, '', $siteID);
                    $type     = $arOption[1];
                    $disabled = array_key_exists("disabled", $arOption) ? $arOption["disabled"] : "";
                    ?>
					<tr <? if (isset($arOption[2]) && strlen($arOption[2]))
                        echo 'style="display:none" class="show-for-'.htmlspecialcharsbx($arOption[2]).'"' ?>>
						<td width="40%" <? if ($type[0] == "textarea")
                            echo 'class="adm-detail-valign-top"' ?>>
							<label for="<? echo htmlspecialcharsbx($name) ?>"><? echo $arOption[0] ?>:</label>
						<td width="30%">
                            <? if ($type[0] == "checkbox"){
                                ?>
								<input type="checkbox"
								       name="<? echo htmlspecialcharsbx($name) ?>"
								       id="<? echo htmlspecialcharsbx($name) ?>"
								       value="Y"<? if ($val == "Y")
                                    echo " checked"; ?><? if ($disabled)
                                    echo ' disabled="disabled"'; ?>><? if ($disabled)
                                    echo '<br>'.$disabled; ?><?
                            } elseif ($type[0] == "text"){
                                ?>
								<input type="text"
								       size="<? echo $type[1] ?>"
								       maxlength="255"
								       value="<? echo htmlspecialcharsbx($val) ?>"
								       name="<? echo htmlspecialcharsbx($name) ?>">
                                <?
                            } elseif ($type[0] == "textarea"){
                                ?>
								<textarea rows="<? echo $type[1] ?>"
								          name="<? echo htmlspecialcharsbx($name) ?>"
								          style="width:100%"><? echo htmlspecialcharsbx($val) ?></textarea>
                                <?
                            } elseif ($type[0] == "select"){
                                ?><? if (count($type[1])){
                                    ?>
									<select name="<? echo htmlspecialcharsbx($name) ?>"
									        onchange="doShowAndHide()">
                                        <? foreach ($type[1] as $key => $value){
                                            ?>
											<option value="<? echo htmlspecialcharsbx($key) ?>" <? if ($val == $key)
                                                echo 'selected="selected"' ?>><? echo htmlspecialcharsEx($value) ?></option>
                                            <?
                                        } ?>
									</select>
                                    <?
                                } else{
                                    ?><? echo GetMessage("ZERO_ELEMENT_ERROR"); ?><?
                                } ?><?
                            } elseif ($type[0] == "note"){
                                ?><? echo BeginNote(), $type[1], EndNote(); ?><?
                            } ?>
						</td>
						<td width="30%">
                            <? if ($arOption[3]){
                                ?>
								<p><? echo $arOption[3]; ?></p>
                                <?
                            } ?>
						</td>
					</tr>
                    <?
                }
            } elseif ($aTab["DIV"] == "variables"){ // здесь просто надо совсем по-другому всЄ показывать
                ?>
				<tr>
					<th><? echo GetMessage("CODE_VIEW"); ?></th>
					<th align="center"><? echo GetMessage("PHP_VIEW"); ?></th>
					<th><? echo GetMessage("DESCRIPTION_VIEW"); ?></th>
				</tr>
                <? foreach ($arVars as $c => $varArr){
                    ?>
					<tr>
						<td>
							<a href="javascript:void(0)"
							   class="copy_to_clipboard"><?=$varArr["CODE"];?></a>
						</td>
						<td align="center"><?=$varArr["PHP"];?></td>
						<td><?=$varArr["DESC"];?></td>
					</tr>
                    <?
                } ?><?
            } elseif ($aTab["DIV"] == "rights"){ // суперкостыль дл¤ правки прав, потому что в битриксе вс¤ форма подключаетс¤ в отдельном файле
                require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
            }
        } ?>

        <? $tabControl->Buttons(); ?>
		<input type="submit"
		       name="Update"
		       value="<?=GetMessage("MAIN_SAVE")?>"
		       title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>"
		       class="adm-btn-save">
        <?=bitrix_sessid_post();?>
        <? $tabControl->End(); ?>
	</form>
	<script>
		function doShowAndHide(){
			var form    = BX('options');
			var selects = BX.findChildren(form, {tag: 'select'}, true);
			for(var i = 0; i < selects.length; i++){
				var selectedValue = selects[i].value;
				var trs           = BX.findChildren(form, {tag: 'tr'}, true);
				for(var j = 0; j < trs.length; j++){
					if (/show-for-/.test(trs[j].className)){
						if (trs[j].className.indexOf(selectedValue) >= 0)
							trs[j].style.display = 'table-row';
						else
							trs[j].style.display = 'none';
					}
				}
			}
		}

		BX.ready(doShowAndHide);

		// копирование текста
		var copyEmailBtns = document.querySelectorAll(".copy_to_clipboard");
		for(var i = 0; i < copyEmailBtns.length; i++){
			copyEmailBtns[i].addEventListener("click", function(e){
				// ¬ыборка текста
				// var textEl = copyEmailBtn.parentElement.nextElementSibling;
				var range = document.createRange();
				range.selectNode(this);
				window.getSelection().addRange(range);

				try {
					// “еперь, когда мы выбрали текст, выполним команду копировани¤
					var successful = document.execCommand("copy");
					if (successful){
						console.log("Copy email command was successful");
					}
				}catch(err) {
					console.log("Oops, unable to copy");
				}

				window.getSelection().removeAllRanges();
			});
		}
	</script>
<? } ?>