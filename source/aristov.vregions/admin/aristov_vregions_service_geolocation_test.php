<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $USER, $APPLICATION, $DB;
$moduleID = 'aristov.vregions';
CModule::IncludeModule('aristov.vregions');

$userIP   = \Aristov\Vregions\Tools::getUserIP();

$APPLICATION->SetTitle(Loc::getMessage("TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); ?>
	<hr>
	<div>
		<b><?=Loc::getMessage('YOUR_IP');?></b><br>
        <?=$userIP;?>
	</div>
	<hr>
	<div>
		<b><?=Loc::getMessage('SELECTED_GEOLOCATION_METHOD');?>:</b><br>
        <?
        $vregions_auto_geoposition_method = \Aristov\VRegions\Tools::getModuleOption('vregions_auto_geoposition_method');
        $vregions_php_geoposition_tool    = \Aristov\VRegions\Tools::getModuleOption('vregions_php_geoposition_tool');
        ?>
        <? if ($vregions_auto_geoposition_method == 'sxgeo'){ ?>
            <?=Loc::getMessage('SELECTED_GEOLOCATION_METHOD_PHP_IP');?>
            <? if ($vregions_php_geoposition_tool == 'sxgeo'){ ?>
				(<?=Loc::getMessage('SELECTED_GEOLOCATION_METHOD_PHP_IP_1');?>)
            <? } elseif ($vregions_php_geoposition_tool == 'ipgeobase'){ ?>
				(<?=Loc::getMessage('SELECTED_GEOLOCATION_METHOD_PHP_IP_2');?>)
            <? } else{ ?>
				<b class="error">(<?=Loc::getMessage('SELECTED_GEOLOCATION_METHOD_PHP_IP_ERROR');?>)</b>
            <? } ?>
        <? } elseif ($vregions_auto_geoposition_method == 'google'){ ?>
            <?=Loc::getMessage('SELECTED_GEOLOCATION_METHOD_HTML5');?>
        <? } else{ ?>
            <?=Loc::getMessage('NOT_SPECIFIED');?>
        <? } ?>
	</div>
<?
$locationArr = \Aristov\Vregions\Tools::getLocationByIP($userIP);
?>
	<hr>
	<div>
		<b><?=Loc::getMessage('GOTTEN_COORDS');?>:</b><br>
        <?=$locationArr['city']['lat'].':'.$locationArr['city']['lon'];?>
	</div>
	<hr>
	<div>
		<b><?=Loc::getMessage('CLOSEST_REGION');?>:</b><br>
        <?php
        $closestRegion = \Aristov\Vregions\Tools::getClosestToCoordsRegion($locationArr['city']['lat'], $locationArr['city']['lon']);
        echo $closestRegion['NAME'];
        ?>
	</div>
	<hr>
	<div>
		<b><?=Loc::getMessage('CURRENT_REGION');?>:</b><br>
        <?=$_SESSION['VREGIONS_REGION']['NAME']?>
	</div>
	<hr>
	<div>
		<b><?=Loc::getMessage('REGION_WITHOUT_COORDS');?>:</b><br>
        <?
        $res = CIBlockElement::GetList(
            Array(
                "SORT" => "ASC"
            ),
            Array(
                'IBLOCK_ID'                                                                                => \Aristov\VRegions\Tools::getModuleOption('vregions_iblock_id'),
                'ACTIVE'                                                                                   => 'Y',
                'PROPERTY_'.\Aristov\VRegions\Tools::getModuleOption('vregions_iblock_region_centre_prop') => false,
            ),
            false,
            false,
            Array()
        );
        echo $count = $res->SelectedRowsCount();
        $comma = '';
        if ($count){
            echo " (";
            while($ob = $res->GetNextElement()){
                $arFields = $ob->GetFields();
                echo $comma.$arFields['NAME'];
                $comma = ', ';
            }
            echo ")";
        }
        ?>
	</div>
	<hr>
	<h3><?=Loc::getMessage('CHECK_IP_GEO_TITLE');?></h3>
	<p><?=Loc::getMessage('CHECK_IP_GEO_DESCRIPTION');?></p>
	<form method="post"
	      enctype="multipart/form-data">
		<input type="text"
		       name="ip"
		       required>
		<input type="submit"
		       name="check_ip"
		       value="<?=Loc::getMessage('CHECK');?>">
	</form>
<?php
if ($_REQUEST['check_ip']){
    $locationArr   = \Aristov\Vregions\Tools::getLocationByIP($_REQUEST['ip']);
    $closestRegion = \Aristov\Vregions\Tools::getClosestToCoordsRegion($locationArr['city']['lat'], $locationArr['city']['lon']);

    echo '<b>'.Loc::getMessage('GOTTEN_COORDS')."</b>:<br>";
    echo $locationArr['city']['lat'].':'.$locationArr['city']['lon']."<br>";
    echo '<b>'.Loc::getMessage('CLOSEST_REGION')."</b>:<br>";
    echo $closestRegion['NAME']."<br>";
}
?>
	<br>
	<hr>
	<h3><?=Loc::getMessage('SXGEO_UPDATE_TITLE');?></h3>
	<p><?=Loc::getMessage('SXGEO_UPDATE_DESCRIPTION');?></p>
	<form method="post"
	      enctype="multipart/form-data">
		<input type="file"
		       name="base"
		       required>
		<input type="submit"
		       name="SxGeoUpdate"
		       value="<?=Loc::getMessage('SAVE');?>">
	</form>
<?php
if ($_POST['SxGeoUpdate']){
    if ($_FILES['base']){
        $uploadDir  = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/aristov.vregions/lib/';
        $uploadFile = $uploadDir.'SxGeoCity.dat';
        if (move_uploaded_file($_FILES['base']['tmp_name'], $uploadFile)){
            echo CAdminMessage::ShowMessage(array(
                "TYPE"    => "OK",
                "MESSAGE" => Loc::getMessage('BASE_UPDATE_SUCCESS'),
                "HTML"    => true,
            ));
        } else{
            echo CAdminMessage::ShowMessage(array(
                "TYPE"    => "OK",
                "MESSAGE" => Loc::getMessage('BASE_UPDATE_FAILURE'),
                "HTML"    => true,
            ));
        }
    }
}
?>
	<hr>
	<h3><?=Loc::getMessage('IPGEOLITE_UPDATE_TITLE');?></h3>
	<p><?=Loc::getMessage('IPGEOLITE_UPDATE_DESCRIPTION');?></p>
	<form method="post"
	      enctype="multipart/form-data">
		<input type="file"
		       name="base"
		       required>
		<input type="submit"
		       name="IpGeoBaseUpdate"
		       value="<?=Loc::getMessage('SAVE');?>">
	</form>
<?php
if ($_POST['IpGeoBaseUpdate']){
    if ($_FILES['base']){
        $uploadDir  = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/aristov.vregions/lib/ipgeobase/';
        $uploadFile = $uploadDir.'cidr_optim.txt';
        if (move_uploaded_file($_FILES['base']['tmp_name'], $uploadFile)){
            echo CAdminMessage::ShowMessage(array(
                "TYPE"    => "OK",
                "MESSAGE" => Loc::getMessage('BASE_UPDATE_SUCCESS'),
                "HTML"    => true,
            ));
        } else{
            echo CAdminMessage::ShowMessage(array(
                "TYPE"    => "OK",
                "MESSAGE" => Loc::getMessage('BASE_UPDATE_FAILURE'),
                "HTML"    => true,
            ));
        }
    }
}
?>
	<hr>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>