<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("ARISTOV_DELIVERY.CALC_COMPONENT_NAME"),
	"DESCRIPTION" => GetMessage("ARISTOV_DELIVERY.CALC_COMPONENT_DESCRIPTION"),
	"ICON" => "/images/regions.gif",
	"SORT" => 500,
	"PATH" => array(
		"ID" => "aristov_vregions_components",
		"SORT" => 500,
		"NAME" => GetMessage("ARISTOV_DELIVERY.CALC_COMPONENTS_FOLDER_NAME"),
	),
);

?>