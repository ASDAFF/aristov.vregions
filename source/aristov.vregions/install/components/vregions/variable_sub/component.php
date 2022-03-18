<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arResult = Array();
$arResult["VALUE"] = $_SESSION["VREGIONS_REGION"][$arParams["SVOYSTVO_INFOBLOKA"]];

if (is_array($arResult["VALUE"])){
	if (isset($arResult["VALUE"]["TYPE"])){
		if (strtolower($arResult["VALUE"]["TYPE"]) == "html"){
			$arResult["VALUE"] = html_entity_decode($arResult["VALUE"]["TEXT"]);
		}
		if (strtolower($arResult["VALUE"]["TYPE"]) == "text"){
			$arResult["VALUE"] = $arResult["VALUE"]["TEXT"];
		}
	}
}
$this->IncludeComponentTemplate(); ?>