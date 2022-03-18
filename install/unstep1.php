<?if(!check_bitrix_sessid()) return;?>
<?IncludeModuleLangFile(__FILE__);?>

<form action="<?echo $APPLICATION->GetCurPage()?>" name="form1">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="hidden" name="id" value="aristov.vregions">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="step" value="2">
	<table cellpadding="3" cellspacing="0" border="0" width="0%">
		<tr>
			<td>&nbsp;</td>
			<td>
				<table cellpadding="3" cellspacing="0" border="0">
				<tr>
					<td><input type="checkbox" name="DELETE_IBLOCK" id="DELETE_IBLOCK" value="Y"></td>
					<td><p><label for="DELETE_IBLOCK"><?echo GetMessage("DELETE_IBLOCK")?></label></p></td>
				</tr>
				</table>
			</td>
		</tr>
	</table>
	<br>
	<input type="submit" name="inst" value="<?echo GetMessage("MOD_UNINST_DEL")?>">
</form>
