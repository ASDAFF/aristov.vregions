<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die(); ?>
<? $this->setFrameMode(true); ?>
<?
$rand = rand();
?>
<? if (!empty($arResult["ITEMS"])){ ?>
	<div class="vr-template <? if ($arParams["FIXED"] == "Y"){ ?>vr-template__fixed<? } ?>"
	     data-rand="<?=$rand;?>">
		<span class="vr-template__label"><?=html_entity_decode($arParams["STRING_BEFORE_REGION_LINK"] ?: GetMessage("YOUR_REGION"));?></span>
		<a class="vr-template__link js-vr-template__link-region-name"
		   href="#"
		   onclick="OpenVregionsPopUp('open', 'vregions-popup<?=$rand;?>', 'vregions-sepia<?=$rand;?>'); return false;"><?=(strlen($arResult["CURRENT_SESSION_ARRAY"]["NAME"]) ? $arResult["CURRENT_SESSION_ARRAY"]["NAME"] : $arResult["DEFAULT"]["NAME"]);?></a>
	</div>
    <? if ($arParams['HIDE_FROM_INDEX'] == 'Y'){ ?>
		<noindex>
    <? } ?>
	<div id="vregions-sepia<?=$rand;?>"
	     class="vregions-sepia"
	     onclick="OpenVregionsPopUp('close'); return false;"></div>
    <? if ($arParams["SHOW_POPUP_QUESTION"] == "Y"){ ?>
        <? include "popup-question.php"; ?>
    <? } ?>
    <? include "popup.php"; ?>
    <? if ($arParams['HIDE_FROM_INDEX'] == 'Y'){ ?>
		</noindex>
    <? } ?>
<? } else{ ?>
    <?=GetMessage("ERROR_OF_NO_ELS");?>
<? } ?>
<script>
	// !! keep function with this name
	function vrAskRegion(region_name, cookie, url_without_path){
		var vregions_popups = document.getElementsByClassName("vregions-popup-que");
		if (vregions_popups[0]){
			OpenVregionsPopUp("close");

			Array.prototype.forEach.call(vregions_popups, function(vregions_popup){
				var region_name_elem     = vregions_popup.getElementsByClassName("js-suggested-region");
				var success_quess_button = vregions_popup.getElementsByClassName("js-we_guessed");
				var sepia                = document.getElementsByClassName("vregions-sepia")[0];

				Array.prototype.forEach.call(region_name_elem, function(nameElem, i){
					nameElem.innerHTML = region_name;
					success_quess_button[i].setAttribute("data-cookie", cookie);
					success_quess_button[i].setAttribute("href", url_without_path);
					success_quess_button[i].setAttribute("data-domain", url_without_path);
				});

				sepia.style.display          = "block";
				vregions_popup.style.display = "block";
			});

			vrAddClass(document.getElementsByTagName('body')[0], 'modal-open');
		}

		return false;
	}
</script>