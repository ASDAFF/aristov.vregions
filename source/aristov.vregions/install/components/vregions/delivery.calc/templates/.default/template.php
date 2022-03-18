<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<div class="vregions-delivery-calc js-vregions-delivery-calc"
     data-product_id="<?=$arParams['ID_TOVARA'];?>"
     data-title="<?=$arParams['TITLE'];?>"
     data-exclude_deliveries="<?=implode(',', $arParams['EXCLUDE_DELIVERIES']);?>"
>
	<input type="hidden"
	       class="js-vregions-delivery-ajax-path"
	       value="<?=$componentPath.'/ajax.php';?>">
    <?php
    if ($arResult['DELIVERIES']){ ?>
		<div class="vregions-delivery-calc__title"><?=$arParams['TITLE'];?></div>
		<div class="vregions-delivery-calc-form-wrapper">
			<div class="vregions-delivery-calc-form">
				<input type="text"
				       name="location_name"
				       class="vregions-delivery-calc-form__location-input js-vregions-delivery-location-input"
				       value="<?=$arResult['LOCATION']['CITY_NAME'];?>"
				>
				<div class="vregions-delivery-calc-form__search-results js-vregions-delivery-search-results-wrap">
				</div>
			</div>
		</div>
		<table class="vregions-delivery-calc__table">
            <? foreach ($arResult['DELIVERIES'] as $delivery){ ?>
				<tr class="vregions-delivery-calc__tr">
					<td class="vregions-delivery-calc__td"><?=$delivery['NAME'];?></td>
					<td class="vregions-delivery-calc__td"><?=$delivery['PERIOD_TEXT'];?></td>
					<td class="vregions-delivery-calc__td"><?=$delivery['PRICE_FORMATED'];?></td>
				</tr>
            <? } ?>
		</table>
    <? } ?>
</div>
