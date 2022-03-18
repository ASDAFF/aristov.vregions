<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("REGIONS_COMPONENT_NAME"),
	"DESCRIPTION" => GetMessage("REGIONS_COMPONENT"),
	"ICON" => "/images/regions.gif",
	"SORT" => 10,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "aristov_vregions_components",
		"SORT" => 2200,
		"NAME" => GetMessage("V_REGIONS_COMPONENTS")
	),
);

?>