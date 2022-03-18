<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $USER, $APPLICATION, $DB;
CModule::IncludeModule('aristov.vregions');

$APPLICATION->SetTitle(Loc::getMessage("TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

\Bitrix\Main\Page\Asset::getInstance()->addJs("https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js");
?>
	<div class="js-result"></div>
	<p><?=Loc::getMessage("TOP_DESCRIPTION");?></p>
	<form method="post" class="js-vregions-yml-form">
		<div class="form-group">
			<label for="from"><?=Loc::getMessage("FROM");?></label>
			<br>
			<input type="text" id="from" name="from" style="width: 100%;"
			       value="<?=Aristov\VRegions\Tools::getModuleOption('yml_file_path_from', '', true);?>" required>
		</div>
		<br>
		<div class="form-group">
			<label for="to"><?=Loc::getMessage("TO");?></label>
			<br>
			<input type="text" id="to" name="to" style="width: 100%;"
			       value="<?=Aristov\VRegions\Tools::getModuleOption('yml_file_path_to', '', true);?>"
			       placeholder="/regional_yml.php" required>
		</div>
		<br>
		<div class="form-group">
			<label for="site_id"><?=Loc::getMessage("SITE_ID");?></label>
			<br>
			<input type="text" id="site_id" name="site_id" style="width: 100%;"
			       value="<?=Aristov\VRegions\Tools::getModuleOption('yml_site_id', '', true);?>" required>
		</div>
		<br>
		<div class="form-group">
			<label for="site_address"><?=Loc::getMessage("SITE_ADDRESS");?></label>
			<br>
			<input type="text" id="site_address" name="site_address" style="width: 100%;"
			       value="<?=Aristov\VRegions\Tools::getModuleOption(
                       'yml_site_address',
                       '',
                       true
                   ) ?: ($_SERVER['HTTP_HOST'] ?: $_SERVER['SERVER_NAME']);?>" required>
		</div>
		<br>
		<button name="generate"
		        value="y"><?=Loc::getMessage("GENERATE");?></button>
	</form>
	<script>
		$(document).on('submit', '.js-vregions-yml-form', function () {
			$('.js-result').html('');
			$.ajax({
				url: "/bitrix/admin/aristov_vregions_yml_ajax.php",
				data: $('.js-vregions-yml-form').serializeArray(),
				type: "post",
				success: function (answer) {
					$('.js-result').html(answer);
				},
				error: function () {
					$('.js-result').html('<div class="adm-info-message-wrap adm-info-message-red">\n' +
						'\t\t\t\t<div class="adm-info-message">\n' +
						'\t\t\t\t\t\n' +
						'\t\t\t\t\t<?=Loc::getMessage("REFRESH");?>' +
						'\t\t\t\t\t<div class="adm-info-message-icon"></div>\n' +
						'\t\t\t\t</div>\n' +
						'\t\t\t</div>');
				}
			});

			return false;
		});
	</script>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>