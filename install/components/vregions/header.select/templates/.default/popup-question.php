<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die(); ?>
<div class="vr-popup vregions-popup-que"
     id="vregions-popup-que<?=$rand;?>">
	<div class="vr-popup__content vr-popup__content_que">
		<!--noindex-->
		<a class="vr-popup__close"
		   onclick="OpenVregionsPopUp('close'); return false;">close
		</a>
		<div class="vr-popup__header">
			<div class="vr-popup__title"><?=$arParams["POPUP_QUESTION_TITLE"] ? $arParams["POPUP_QUESTION_TITLE"] : GetMessage("DID_WE_GUESS");?></div>
		</div>
		<div class="vr-popup__body clearfix">
			<div class="vr-popup__paragraph"><?=$arParams["POPUP_QUESTION_YOUR_REGION_IS"] ? $arParams["POPUP_QUESTION_YOUR_REGION_IS"] : GetMessage("YOUR_REGION_IS");?>
				<span class="vr-popup__suggested-region js-suggested-region"></span>
				<b>?</b>
			</div>
			<div class="vr-popup__que-buttons-wrapper">
				<a href="#"
				   onclick="ChangeVRegion(this); return false;"
				   data-domain=""
				   data-cookie=""
				   class="vr-popup__button js-we_guessed"><?=$arParams["POPUP_QUESTION_YES_BUTTON_TEXT"] ? $arParams["POPUP_QUESTION_YES_BUTTON_TEXT"] : GetMessage("YES_MY_REGION");?></a>
				<a class="vr-popup__button vr-popup__button_danger"
				   onclick="OpenVregionsPopUp('open', 'vregions-popup<?=$rand;?>', 'vregions-sepia<?=$rand;?>');"><?=$arParams["POPUP_QUESTION_NO_BUTTON_TEXT"] ? $arParams["POPUP_QUESTION_NO_BUTTON_TEXT"] : GetMessage("NOT_MY_REGION");?></a>
			</div>
		</div>
		<!--/noindex-->
	</div>
</div>
