<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = array(
	"NAME"        => GetMessage("ARISTOV_VARIABLE_SUB_COMPONENT_NAME"),
	"DESCRIPTION" => GetMessage("ARISTOV_VARIABLE_SUB_COMPONENT_DESCRIPTION"),
	"ICON"        => "/images/regions.gif",
	"SORT"        => 500,
	"PATH"        => array(
		"ID"   => "aristov_vregions_components",
		"SORT" => 500,
		"NAME" => GetMessage("ARISTOV_VARIABLE_SUB_COMPONENTS_FOLDER_NAME"),
	),
);

?>