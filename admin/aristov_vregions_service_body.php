<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $USER, $APPLICATION, $DB;
$APPLICATION->SetTitle(Loc::getMessage("TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

CModule::IncludeModule('aristov.vregions');

if (\AristovVregionsHelper::isDemoEnd()){
    exit;
}

//$APPLICATION->SetAdditionalCSS("/bitrix/css/main/bootstrap.css");

$aTabs = array(
    array(
        "DIV"   => "edit1",
        "TAB"   => Loc::getMessage("SITEMAP"),
        "ICON"  => "",
        "TITLE" => Loc::getMessage("SITEMAP")
    ),
    array(
        "DIV"   => "edit2",
        "TAB"   => Loc::getMessage("ROBOTS"),
        "ICON"  => "",
        "TITLE" => Loc::getMessage("ROBOTS")
    ),
    array(
        "DIV"   => "edit3",
        "TAB"   => Loc::getMessage("ROBOTS_DIFFICULT"),
        "ICON"  => "",
        "TITLE" => Loc::getMessage("ROBOTS_DIFFICULT")
    )
);

if (isset($_POST["GENERATE_ROBOTS"])){
    //vprint($_POST);
    $answer = Aristov\VRegions\Tools::generate_robots($_POST["DOMAIN"], ($_POST["ONLY_HTTPS"] == 'Y'), ($_POST["MAKE_PHP_FILE"] == 'Y'));
    if ($answer["success"] === true){
        echo CAdminMessage::ShowNote(Loc::getMessage("ROBOTS_CREATED"));
    } else{
        echo CAdminMessage::ShowMessage(array(
            "TYPE"    => "ERROR",
            "DETAILS" => Loc::getMessage("ROBOTS_".$answer["message"]),
            "HTML"    => true,
        ));
    }
}

if (isset($_POST["GENERATE_MAP"])){
    Aristov\VRegions\Tools::setModuleOption('sitemap_files', serialize($_POST['CURRENT_MAP_PATH']));
    Aristov\VRegions\Tools::setModuleOption('sitemap_domain', $_POST['DOMAIN']);
    foreach ($_POST['CURRENT_MAP_PATH'] as $path){
        if (Aristov\VRegions\Tools::sitemap_gen($path, $_POST["DOMAIN"])){
            echo CAdminMessage::ShowNote($path.': '.strtolower(Loc::getMessage("SITEMAP_CREATED")));
        } else{
            echo CAdminMessage::ShowMessage(array(
                "TYPE"    => "ERROR",
                "MESSAGE" => $path.': '.strtolower(Loc::getMessage("SITEMAP_DIDNT_CREATED")),
                "HTML"    => true,
            ));
        }
    }
}

if (isset($_POST["GENERATE_ROBOTS_DIFFICULT"])){
    $answer = Aristov\VRegions\Tools::generate_robots_difficult($_POST["PROP"], ($_POST["MAKE_PHP_FILE"] == 'Y'), ($_POST["REPLACE_HOST_ADDRESS"] == 'Y'));
    if ($answer["success"] === true){
        echo CAdminMessage::ShowNote(Loc::getMessage("ROBOTS_CREATED"));
    } else{
        echo CAdminMessage::ShowMessage(array(
            "TYPE"    => "ERROR",
            "DETAILS" => Loc::getMessage("ROBOTS_".$answer["message"]),
            "HTML"    => true,
        ));
    }
}

if (isset($_POST["SET_TYPICAL_ROBOTS"])){
    if ($_POST['ROBOTS_PROP'] && $_POST['ROBOTS_CONTENT']){
        $res = CIBlockElement::GetList(
            Array(
                "SORT" => "ASC"
            ),
            Array(
                'IBLOCK_ID' => \Aristov\VRegions\Tools::getModuleOption("vregions_iblock_id"),
                'ACTIVE'    => 'Y',
            ),
            false,
            false,
            Array(
                'ID',
                'CODE',
            )
        );
        while($ob = $res->GetNextElement()){
            $arFields      = $ob->GetFields();
            $robotsContent = $_POST['ROBOTS_CONTENT'];
            $robotsContent = str_replace(
                Array(
                    'http://'.$_SERVER["HTTP_HOST"],
                    'https://'.$_SERVER["HTTP_HOST"],
                ),
                Aristov\VRegions\Tools::generateRegionLink($arFields['CODE']),
                $robotsContent
            );

            CIBlockElement::SetPropertyValuesEx(
                $arFields['ID'],
                false,
                array(
                    $_POST['ROBOTS_PROP'] => Array(
                        'VALUE' => array(
                            'TYPE' => 'HTML',
                            'TEXT' => $robotsContent
                        )
                    )
                )
            );
        }
    }
}

//if (!\Aristov\VRegions\Tools::ifPhpInTxtWorks()){
//    echo CAdminMessage::ShowMessage(array(
//        "TYPE"    => "ERROR",
//        "MESSAGE" => Loc::getMessage("PHP_IN_TXT_DOESNT_WORK"),
//        "HTML"    => true,
//    ));
//}

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<form action=""
	      method="POST">
        <?php
        $allSitemaps = Array();
        $files       = scandir($_SERVER['DOCUMENT_ROOT']);
        foreach ($files as $file){
            if (strpos($file, 'sitemap') !== false && strpos($file, '.xml') !== false){
                $allSitemaps[] = $file;
            }
        }
        ?>
        <? if (!empty($allSitemaps)){
            $chosenMaps = unserialize(Aristov\VRegions\Tools::getModuleOption('sitemap_files', '', true));
            ?>
			<div class="form-group">
				<label for="CURRENT_MAP_PATH"><?=Loc::getMessage("OLD_SITEMAP_ADDRESS");?></label>
				<br>
				<select id="CURRENT_MAP_PATH"
				        name="CURRENT_MAP_PATH[]"
				        class="form-control"
				        multiple
				        required
				>
                    <? foreach ($allSitemaps as $sitemap){ ?>
						<option value="<?=$sitemap;?>"
                            <?php
                            if (in_array($sitemap, $chosenMaps)){
                                echo 'selected';
                            }
                            ?>
						><?=$sitemap;?></option>
                    <? } ?>
				</select>
			</div>
        <? } ?>
		<br>
		<div class="form-control">
			<label for="DOMAIN"><?=Loc::getMessage("DOMAIN");?></label>
			<br>
			<input type="text"
			       name="DOMAIN"
			       id="DOMAIN"
			       value="<?=Aristov\VRegions\Tools::getModuleOption('sitemap_domain', '', true) ?: preg_replace("/\:\d+/is", "", $_SERVER["HTTP_HOST"]);?>"
			       required>
		</div>
		<br>
		<div class="form-control">
			<button class="btn btn-primary"
			        name="GENERATE_MAP"><?=Loc::getMessage("CREATE");?></button>
		</div>
	</form>
	<p><?=Loc::getMessage("GENERATE_MAP_DESCRIPTION", Array("HTTP_HOST" => $_SERVER["HTTP_HOST"]));?></p>
<?php
$maps = unserialize(Aristov\VRegions\Tools::getModuleOption('sitemap_files', '', true));
if (!empty($maps)){
    ?>
	<h2><?=Loc::getMessage("DONT_FORGET_SITEMAP_HTACCESS_HEADER");?></h2>
	<p>
        <?=Loc::getMessage("DONT_FORGET_SITEMAP_HTACCESS");?>
	<pre>
<? $maps = unserialize(Aristov\VRegions\Tools::getModuleOption('sitemap_files', '', true));
foreach ($maps as $map){
    ; ?>
	RewriteRule ^<?=str_replace('.xml', '\.xml', $map);?>$ /<?=str_replace('.xml', '.php', $map);?> [L]
<? } ?>
</pre>
<? } ?>
	</p>
	<h2><?=Loc::getMessage("AUTO_SITEMAP_GENERATION_HEADER");?></h2>
	<p>
        <?=Loc::getMessage("AUTO_SITEMAP_GENERATION");?>
	</p>
	<img src="http://av-promo.ru/upload/medialibrary/20a/20a5a8806203158a0cf54470780bd023.png"
	     alt="<?=Loc::getMessage("AUTO_SITEMAP_GENERATION_IMG_TITLE");?>"
	     title="<?=Loc::getMessage("AUTO_SITEMAP_GENERATION_IMG_TITLE");?>">
<? $tabControl->BeginNextTab(); ?>
	<form action=""
	      method="POST">
		<div class="form-control">
			<label for="DOMAIN"><?=Loc::getMessage("DOMAIN");?></label>
			<br>
			<input type="text"
			       name="DOMAIN"
			       id="DOMAIN"
			       value="<?=preg_replace("/\:\d+/is", "", $_SERVER["HTTP_HOST"]);?>"
			       required>
		</div>
		<br>
		<div class="form-control">
			<label>
				<input type="checkbox"
				       name="ONLY_HTTPS"
				       id="ONLY_HTTPS"
				       value="Y">
                <?=Loc::getMessage("ALL_LINKS_ONLY_HTTPS");?>
			</label>
		</div>
		<br>
		<div class="form-control">
			<label style="display: none;">
				<input type="checkbox"
				       name="MAKE_PHP_FILE"
				       id="MAKE_PHP_FILE"
				       value="Y"
				       checked
				>
                <?=Loc::getMessage("MAKE_PHP_FILE");?>
			</label>
			<p><b><?=Loc::getMessage("MAKE_PHP_FILE_DESC");?></b></p>
		</div>
		<br>
		<div class="form-control">
			<button class="btn btn-primary"
			        name="GENERATE_ROBOTS"><?=Loc::getMessage("MAKE_DYN");?></button>
		</div>
	</form>
	<p><?=Loc::getMessage("GENERATE_ROBOTS_SIMPLE_DESCRIPTION", Array("HTTP_HOST" => $_SERVER["HTTP_HOST"]));?></p>
<? $tabControl->BeginNextTab(); ?>
	<form action=""
	      method="POST">
		<div class="form-control">
			<label for="PROP"><?=Loc::getMessage("ROBOTS_PROP");?></label>
			<br>
			<select name="PROP"
			        id="PROP">
                <?php
                $properties = CIBlockProperty::GetList(Array(
                    "SORT" => "ASC",
                    "NAME" => "ASC"
                ), Array(
                    "ACTIVE"    => "Y",
                    "IBLOCK_ID" => COption::GetOptionString("aristov.vregions", "vregions_iblock_id")
                ));
                while($prop_fields = $properties->GetNext()){
                    ?>
					<option value="<?=$prop_fields["CODE"];?>"
                        <?php
                        if (stripos($prop_fields["CODE"], 'robots') !== false){
                            echo "selected";
                        }
                        ?>
					><?=$prop_fields["NAME"];?></option>
                    <?
                }
                ?>
			</select>
		</div>
		<br>
		<div class="form-control">
			<label>
				<input type="checkbox"
				       name="REPLACE_HOST_ADDRESS"
				       id="REPLACE_HOST_ADDRESS"
				       value="Y"
				>
                <?=Loc::getMessage("REPLACE_HOST_ADDRESS");?>
			</label>
			<p><?=Loc::getMessage("REPLACE_HOST_ADDRESS_DESC");?></p>
		</div>
		<div class="form-control">
			<label style="display: none;">
				<input type="checkbox"
				       name="MAKE_PHP_FILE"
				       id="MAKE_PHP_FILE"
				       value="Y"
				       checked
				>
                <?=Loc::getMessage("MAKE_PHP_FILE");?>
			</label>
			<p><b><?=Loc::getMessage("MAKE_PHP_FILE_DESC");?></b></p>
		</div>
		<br>
		<div class="form-control">
			<button class="btn btn-primary"
			        name="GENERATE_ROBOTS_DIFFICULT"><?=Loc::getMessage("MAKE_DYN");?></button>
		</div>
	</form>
	<br>
	<p><?=Loc::getMessage("GENERATE_ROBOTS_DIFFICULT_DESCRIPTION", Array("HTTP_HOST" => $_SERVER["HTTP_HOST"]));?></p>
	<form method="POST">
		<div><b><?=Loc::getMessage("SET_TYPICAL_ROBOTS_TITLE");?></b></div>
		<br>
		<div class="form-control">
			<label for="ROBOTS_PROP"><?=Loc::getMessage("ROBOTS_PROP");?></label>
			<br>
			<select name="ROBOTS_PROP"
			        id="ROBOTS_PROP">
                <?php
                $properties = CIBlockProperty::GetList(Array(
                    "SORT" => "ASC",
                    "NAME" => "ASC"
                ), Array(
                    "ACTIVE"    => "Y",
                    "IBLOCK_ID" => COption::GetOptionString("aristov.vregions", "vregions_iblock_id")
                ));
                while($prop_fields = $properties->GetNext()){
                    ?>
					<option value="<?=$prop_fields["CODE"];?>"
                        <?php
                        if (stripos($prop_fields["CODE"], 'robots') !== false){
                            echo "selected";
                        }
                        ?>
					><?=$prop_fields["NAME"];?></option>
                    <?
                }
                ?>
			</select>
		</div>
		<br>
		<div class="form-control">
			<label><?=Loc::getMessage("ROBOTS_CONTENT");?></label>
			<br>
			<textarea name="ROBOTS_CONTENT"
			          id="ROBOTS_CONTENT"
			          cols="50"
			          rows="30"></textarea>
		</div>
		<br>
		<div class="form-control">
			<button class="btn btn-primary"
			        value="y"
			        name="SET_TYPICAL_ROBOTS"><?=Loc::getMessage("SET_TYPICAL_ROBOTS_BTN");?></button>
		</div>
	</form>
<? $tabControl->End(); ?><? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>