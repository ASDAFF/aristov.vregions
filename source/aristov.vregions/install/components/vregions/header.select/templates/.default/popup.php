<div class="vr-popup <?=($arParams['FIT_IN_SCREEN'] == 'Y' ? 'vr-popup_fit' : '');?>"
     id="vregions-popup<?=$rand;?>">
	<div class="vr-popup__content">
		<a class="vr-popup__close"
		   onclick="OpenVregionsPopUp('close'); return false;">
		</a>
		<div class="vr-popup__header">
			<div class="vr-popup__title"><?=$arParams['REGION_POPUP_TITLE'] ?: GetMessage("SELECT_YOUR_REGION");?></div>
		</div>
		<div class="vr-popup__body clearfix">
			<div class="vr-popup__search-wrap js-vregions-search-wrap">
                <? if ($arParams["SHOW_SEARCH_FORM"] == 'Y'){ ?>
					<input type="text"
					       class="vr-popup__search-input js-vregions-search-input"
					       placeholder="<?=$arParams['FIND_REGION_TITLE'] ?: GetMessage("FIND_YOUR_REGION");?>">
                <? } ?>
			</div>
            <? if (count($arResult["CHOSEN_ITEMS"]) && $arParams['SHOW_CHOSEN_ITEMS_BLOCK'] == 'Y'){ ?>
                <? if ($arParams['CHOSEN_ITEMS_TITLE']){ ?>
					<div class="vr-popup_inner-title"><?=$arParams['CHOSEN_ITEMS_TITLE'];?></div>
                <? } ?>
				<div class="vregions-chosen-list clearfix">
                    <? foreach ($arResult["CHOSEN_ITEMS"] as $arItem){ ?>
						<a class="vregions-chosen-list__item <?=$arResult['CHOSEN_ITEMS_CLASS'];?>"
						   href="<?=$arItem["HREF"].$arItem['PATH'];?>"
						   data-domain="<?=$arItem["HREF"];?>"
						   data-cookie="<?=$arItem["~CODE"];?>"
						   onclick="ChangeVRegion(this); return false;"><?=$arItem["NAME"];?></a>
                    <? } ?>
				</div>
            <? } ?>
            <? if ($arParams['ALLOW_OBLAST_FILTER'] == 'Y' && $arParams['SHOW_OBLAST_LEFT'] != 'Y'){ ?>
				<div class="vregions-oblast">
					<label><?=$arParams['OBLAST_SELECT_NAME'] ?: GetMessage('OBLAST');?></label>
					<select name="VREGIONS_OBLAST"
					        class="vregions-oblast__select js-vregions-oblast__select">
						<option value=""><?=GetMessage('ALL');?></option>
                        <? foreach ($arResult["OBLASTI"] as $oblast){ ?>
							<option value="<?=$oblast;?>"><?=$oblast;?></option>
                        <? } ?>
					</select>
				</div>
            <? } ?>
            <? if ($arParams['OTHER_REGIONS_TITLE']){ ?>
				<div class="vr-popup_inner-title"><?=$arParams['OTHER_REGIONS_TITLE'];?></div>
            <? } ?>
			<div class="vregions-list clearfix">
                <? if ($arParams['ALLOW_OBLAST_FILTER'] == 'Y' && $arParams['SHOW_OBLAST_LEFT'] == 'Y'){ ?>
					<div class="vregions-list__col <?=$arResult["COL_CLASS"];?> vregions-list__col_oblasti">
                        <? foreach ($arResult["OBLASTI"] as $oblast){ ?>
							<a href="#" class="vr-popup__oblast-link js-vr-popup__oblast-link"
							   data-oblast="<?=$oblast;?>"><?=$oblast;?></a>
                        <? } ?>
					</div>
                <? } ?>
                <?
                foreach ($arResult["COLS"] as $items){
                    $prevFirstLetter = '';
                    ?>
					<div class="vregions-list__col <?=$arResult["COL_CLASS"];?>">
                        <?
                        foreach ($items as $arItem){
                            $firstLetter = substr($arItem['NAME'], 0, 1);
                            if ($firstLetter !== $prevFirstLetter){
                                if ($arParams['SHOW_LETTER_HEADINGS'] == 'Y'){
                                    if ($prevFirstLetter != ''){
                                        echo '</div>'; ?>
                                    <? }
                                    echo '<div class="vr-popup__regions-letter-block js-vr-popup__regions-letter-block">';
                                    ?>
	                                <div class="vr-popup__regions-letter-heading js-vr-popup__regions-letter-heading <?=($prevFirstLetter == '' ? 'vr-popup__regions-letter-heading_first' : '');?>">
                                        <?=$firstLetter;?>
									</div>
                                    <?
                                }
                                $prevFirstLetter = $firstLetter;
                            }
                            ?>
							<a class="vr-popup__region-link
							<?=($arItem["ACTIVE"] ? 'vr-popup__region-link_active' : '');?>
							js-vr-popup__region-link
							<?=($arItem["CHOSEN_ONE"] == 'Y' ? 'vr-popup__region-link_chosen' : '');?>
"
							   href="<?=$arItem["HREF"].$arItem['PATH'];?>"
							   data-domain="<?=$arItem["HREF"];?>"
							   data-cookie="<?=$arItem["~CODE"];?>"
                                <? if ($arParams['ALLOW_OBLAST_FILTER'] == 'Y'){ ?>
									data-oblast="<?=$arItem['OBLAST'];?>"
                                <? } ?>
                                <? if ($arParams['SHOW_LETTER_HEADINGS'] == 'Y'){ ?>
									data-first_letter="<?=$firstLetter;?>"
                                <? } ?>
                               onclick="ChangeVRegion(this); return false;"><?=$arItem["NAME"];?></a>
                        <? } ?>
                        <? if ($arParams['SHOW_LETTER_HEADINGS'] == 'Y'){
                            echo '</div>'; ?>
                        <? } ?>
					</div>
                <? } ?>
			</div>
            <? if ($arParams['SHOW_ANOTHER_REGION_BTN'] == 'Y'){ ?>
				<div class="vregions-another-region">
					<a href="#"
					   class="vregions-another-region__btn js-another-region-btn">
                        <?=GetMessage('ANOTHER_REGION_BTN');?><br>
						<small><?=GetMessage('ANOTHER_REGION_ADDITION');?></small>
					</a>
				</div>
            <? } ?>
		</div>
	</div>
</div>
