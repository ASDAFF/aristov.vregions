<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $USER, $APPLICATION, $DB;
$moduleID = 'aristov.vregions';
CModule::IncludeModule('aristov.vregions');

$APPLICATION->SetTitle(Loc::getMessage("TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

CModule::IncludeModule('iblock');

\Bitrix\Main\Page\Asset::getInstance()->addJs("https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js");
?>
<?php

// получаем список цен
$prices      = Array();
$dbPriceType = CCatalogGroup::GetList(
    array("NAME" => "ASC")
);
while($arPriceType = $dbPriceType->Fetch()){
    $prices[$arPriceType['ID']] = $arPriceType;
}

// обработка формы
if (isset($_REQUEST['fill_prices'])){

    $iblockID    = $_REQUEST['iblock'];
    $fromPriceID = $_REQUEST['from_price'];
    $toPriceID   = $_REQUEST['to_price'];
    $multiplier  = str_replace(Array(
        ',',
        ' '
    ), '.', $_REQUEST['multiplier']); // сразу же делаем числом

    $productIds = Array();
    $res        = CIBlockElement::GetList(
        Array(
            "SORT" => "ASC"
        ),
        Array(
            'IBLOCK_ID' => $iblockID,
        ),
        false,
        false,
        Array(
            'ID',
        )
    );
    while($ob = $res->GetNextElement()){
        $arFields = $ob->GetFields();

        $productIds[] = $arFields['ID'];
    }

    // отправляем по аяксу
    $packetLength = 50;
    foreach (array_chunk($productIds, $packetLength) as $packet){ ?>
		<script>
			$(document).ready(function(){
				var data = {
					toPriceName: '<?=$prices[$toPriceID]['NAME_LANG'];?>',
					productIds : '<?=implode(',', $packet);?>',
					fromPriceID: '<?=$fromPriceID;?>',
					toPriceID  : '<?=$toPriceID;?>',
					multiplier : '<?=$multiplier;?>',
				};
				console.log(data);

				$.ajax({
					url    : "/bitrix/admin/aristov_vregions_regional_prices_cities_ajax.php",
					data   : data,
					type   : "post",
					success: function(answer){
						console.log(answer)
						$('.js-result').append(answer);
					}
				});
			});
		</script>
    <? } ?>
	<div class="js-result"></div>
	<a href="/bitrix/admin/aristov_vregions_regional_prices_cities.php"><?=Loc::getMessage("GET_BACK");?></a>
    <?
}// вывод формы
else{
    $iblocks = Array();
    $res     = CIBlock::GetList(
        Array(
            "NAME" => "ASC"
        ),
        Array(
            'ACTIVE' => 'Y',
        ), true
    );
    while($ar_res = $res->Fetch()){
        $iblocks[] = $ar_res;
    }
    ?>
	<form method="post">
		<div class="form-group">
			<label for="iblock"><?=GetMessage('IBLOCK_FIELD');?></label>
			<select id="iblock"
			        name="iblock"
			        class="form-control"
			        required>
				<option value=""><?=GetMessage('SELECT');?></option>
                <? foreach ($iblocks as $iblock){ ?>
					<option value="<?=$iblock['ID'];?>"><?=$iblock['NAME'];?> (<?=$iblock['IBLOCK_TYPE_ID'];?>)</option>
                <? } ?>
			</select>
		</div>
		<br>
		<hr>
		<br>
		<div class="form-group">
			<label for="to_price"><?=GetMessage('TO_PRICE_FIELD');?></label>
			<select id="to_price"
			        name="to_price"
			        class="form-control"
			        required>
				<option value=""><?=GetMessage('SELECT');?></option>
                <? foreach ($prices as $price){ ?>
					<option value="<?=$price['ID'];?>"><?=$price['NAME_LANG'];?></option>
                <? } ?>
			</select>
		</div>
		<br>
		<hr>
		<br>
		<div class="form-group">
			<label for="from_price"><?=GetMessage('FROM_PRICE_FIELD');?></label>
			<select id="from_price"
			        name="from_price"
			        class="form-control"
			        required>
				<option value=""><?=GetMessage('SELECT');?></option>
                <? foreach ($prices as $price){ ?>
					<option value="<?=$price['ID'];?>"
                        <? if ($price['BASE'] == 'Y'){ ?>
							selected
                        <? } ?>
					><?=$price['NAME_LANG'];?></option>
                <? } ?>
			</select>
		</div>
		<br>
		<hr>
		<br>
		<div class="form-group">
			<label for="multiplier"><?=GetMessage('MULTIPLIER');?></label>
			<input type="text"
			       id="multiplier"
			       name="multiplier"
			       class="form-control"
			>
		</div>
		<br>
		<div class="form-group">
			<label for="multiplier"><?=GetMessage('OR_MULTIPLIER_FROM_SECTION');?></label>
			<input type="checkbox"
			       id="multiplier_from_section"
			       name="multiplier_from_section"
			       class="form-control"
			>
		</div>
		<div class="description"><?=GetMessage('MULTIPLIER_FROM_SECTION_DESCRIPTION');?></div>
		<br>
		<hr>
		<br>
		<div class="form-group">
			<button id="fill_prices"
			        name="fill_prices"
			        class="btn btn-success"><?=GetMessage('BTN_FILL_PRICES');?>
			</button>
		</div>
	</form>
<? } ?>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>