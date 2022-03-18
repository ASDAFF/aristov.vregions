<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $USER, $APPLICATION, $DB;
$moduleID = 'aristov.vregions';
CModule::IncludeModule('aristov.vregions');

$userIP = \Aristov\Vregions\Tools::getUserIP();

$APPLICATION->SetTitle(Loc::getMessage("TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); ?>
<?php
$events = Array(
	Array(
		'MODULE'      => 'main',
		'EVENT'       => 'OnProlog',
		'EXPLANATION' => Loc::getMessage('EXPLANATION_MAIN_ONPROLOG'),
		'TREATMENT'   => Loc::getMessage('TREATMENT_MAIN_ONPROLOG'),
	),
	Array(
		'MODULE'      => 'main',
		'EVENT'       => 'OnEpilog',
		'EXPLANATION' => Loc::getMessage('EXPLANATION_MAIN_ONEPILOG'),
		'TREATMENT'   => Loc::getMessage('TREATMENT_MAIN_ONEPILOG'),
	),
	Array(
		'MODULE'      => 'catalog',
		'EVENT'       => 'OnGetOptimalPrice',
		'EXPLANATION' => Loc::getMessage('EXPLANATION_CATALOG_ONGETOPTIMALPRICE'),
		'TREATMENT'   => Loc::getMessage('TREATMENT_CATALOG_ONGETOPTIMALPRICE'),
	),
	Array(
		'MODULE'      => 'sale',
		'EVENT'       => 'OnSaleComponentOrderProperties',
		'EXPLANATION' => Loc::getMessage('EXPLANATION_SALE_ONSALECOMPONENTORDERPROPERTIES'),
		'TREATMENT'   => Loc::getMessage('TREATMENT_SALE_ONSALECOMPONENTORDERPROPERTIES'),
	),
    Array(
        'MODULE'      => 'main',
        'EVENT'       => 'OnEndBufferContent',
        'EXPLANATION' => Loc::getMessage('EXPLANATION_MAIN_ONENDBUFFERCONTENT'),
        'TREATMENT'   => Loc::getMessage('TREATMENT_MAIN_ONENDBUFFERCONTENT'),
    ),
    Array(
        'MODULE'      => 'main',
        'EVENT'       => 'OnBeforeEventAdd',
        'EXPLANATION' => Loc::getMessage('EXPLANATION_MAIN_ONBEFOREEVENTADD'),
        'TREATMENT'   => Loc::getMessage('TREATMENT_MAIN_ONBEFOREEVENTADD'),
    ),
    Array(
        'MODULE'      => 'sale',
        'EVENT'       => 'OnSaleBasketItemBeforeSaved',
        'EXPLANATION' => Loc::getMessage('EXPLANATION_SALE_ONSALEBASKETITEMBEFORESAVED'),
        'TREATMENT'   => Loc::getMessage('TREATMENT_SALE_ONSALEBASKETITEMBEFORESAVED'),
    ),
);

foreach ($events as $event){
	$isset = false;

	$handlers = \Bitrix\Main\EventManager::getInstance()->findEventHandlers($event['MODULE'], $event['EVENT']);
	foreach ($handlers as $handler){
		if ($handler['TO_MODULE_ID'] == $moduleID){
			$isset = true;
			break;
		}
		if (strpos($handler['TO_NAME'], '\Aristov\Vregions') !== false){ // novyj sposob
			$isset = true;
			break;
		}
	}

	if ($isset){
		echo CAdminMessage::ShowMessage(array(
			"TYPE"    => "OK",
			"MESSAGE" => Loc::getMessage('HANDLER_ON_EVENT').' '.$event['MODULE'].':'.$event['EVENT'].' '.Loc::getMessage(('ISSET')),
			"HTML"    => true,
		));
	}else{
		echo CAdminMessage::ShowMessage(array(
			"TYPE"    => "ERROR",
			"MESSAGE" => Loc::getMessage('HANDLER_ON_EVENT').' '.$event['MODULE'].':'.$event['EVENT'].' '.Loc::getMessage(('NOT_ISSET')),
			"HTML"    => true,
		));
		?>
		<p><?=$event['EXPLANATION'];?></p>
		<? if ($event['TREATMENT']){ ?>
			<h3><?=Loc::getMessage('HOW_TO_CURE');?></h3>
			<p><?=$event['TREATMENT'];?></p>
			<?
		} ?>
		<?
	}
	?>
	<hr>
	<?
}
?>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>