<?php

use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class CvRegionsOnPageLoad{

	static $MODULE_ID = "aristov.vregions";

	public static function vRegionsMainHandler(){
		global $APPLICATION;
		CModule::IncludeModule("iblock");

		// na sluchaj esli kto-to pytaetsya otkryt' sajt po ip, to ne delaem nichego
		if (preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $_SERVER['SERVER_NAME'])){
			return;
		}

		$IBLOCK_ID = COption::GetOptionString("aristov.vregions", "vregions_iblock_id");
		$DEFAULT_REGION_CODE = COption::GetOptionString("aristov.vregions", "vregions_default");
		$errorPage = COption::GetOptionString("aristov.vregions", "vregions_error_page");
		$handleErrors = (COption::GetOptionString("aristov.vregions", "vregions_error_handle") == "Y") ? 1 : 0;
		$needCookieRedirect = (COption::GetOptionString("aristov.vregions", "vregions_auto_redirect") == "Y") ? 1 : 0;
		$needCookieRedirectOnlyMain = (COption::GetOptionString("aristov.vregions", "vregions_auto_redirect_not_main") == "Y") ? 1 : 0;
		$langProp = COption::GetOptionString("aristov.vregions", "vregions_iblock_region_lang_prop");
		$subdomainLevel = intval(COption::GetOptionString("aristov.vregions", "vregions_subdomain_level"));
		if (!$subdomainLevel){
			$subdomainLevel = 3; // default
		}
		$useSesionCache = (COption::GetOptionString("aristov.vregions", "vregions_use_session_cache") == "Y") ? 1 : 0;

		$www = "";
		$domains = explode(".", $_SERVER['SERVER_NAME']);
		if ($domains[0] == "www"){ // est' li www
			$www = "www.";
		}

		$regionCode = $domains[count($domains) - ($subdomainLevel)]; // poddomen
		// proverka na sluchaj esli poddomena voobshche net
		if ($regionCode == $domains[count($domains) - ($subdomainLevel - 1)]){
			$regionCode = "";
		}
		if ($regionCode == 'www'){ // inache pri rabote po www vsyo upadet
			$regionCode = "";
		}

		// adres sajta bez poddomena
		$hostWOwww = "";
		for ($i = $subdomainLevel - 1; $i > 0; $i--){
			$hostWOwww .= $domains[count($domains) - $i];
			if ($i != 1){
				$hostWOwww .= ".";
			}
		}
		$host = $www.$hostWOwww;

		//echo $subdomainLevel;
		//echo "<br>";
		//echo $regionCode;
		//echo "<br>";
		//echo $host;

		// echo "regionCode => #".$regionCode."#";

		// proverka na zapomnennyj region
		if ($needCookieRedirect){
			$subdomainCookie = $APPLICATION->get_cookie("VREGION_SUBDOMAIN");
			// echo "subdomainCookie => #".$subdomainCookie."<br>";
			// esli yuzer pereshyol po pryamoj ssylke na chuzhoj region
			if (strlen($subdomainCookie)){
				if ($subdomainCookie == $DEFAULT_REGION_CODE){
					$subdomainForURL = "";
					$subdomainForCompare = "";
				}else{
					$subdomainForCompare = $subdomainCookie;
					$subdomainForURL = $subdomainCookie.".";
				}
				if ($subdomainForCompare != $regionCode){ // esli region ne rannee vybrannyj
					// echo $regionCode."<br>".$DEFAULT_REGION_CODE;
					// die();
					if ($regionCode && $regionCode != $DEFAULT_REGION_CODE){ // ne osnovnoj region
						// echo $needCookieRedirectOnlyMain;
						// die();
						if ($needCookieRedirectOnlyMain){ // mozhno perevodit' so vsekh regionov
							$host = $www.$subdomainForURL.$hostWOwww.$_SERVER["REQUEST_URI"];
							header("Location: http://".$host);
						}
					}else{ // tut v principe vsegda ok
						$host = $www.$subdomainForURL.$hostWOwww.$_SERVER["REQUEST_URI"];
						header("Location: http://".$host);
					}
				}
			}
		}

		// sobiraem dannye dlya regiona po umolchaniyu (chtoby nichego ne ekhalo iz-za krivorukosti zapolneniya i ot oshibok v sisteme)
		$regionDefault = Array();
		if (!is_array($_SESSION["VREGIONS_DEFAULT"]) || !$useSesionCache){
			$res = CIBlockElement::GetList(Array(), Array("IBLOCK_ID" => $IBLOCK_ID, "CODE" => $DEFAULT_REGION_CODE), false, false, Array());
			if ($ob = $res->GetNextElement()){
				$arFieldsDefault = $ob->GetFields();
				$arPropsDefault = $ob->GetProperties();
				$regionDefault["ID"] = $arFieldsDefault["ID"];
				$regionDefault["NAME"] = $arFieldsDefault["NAME"];
				$regionDefault["CODE"] = $arFieldsDefault["CODE"];
				foreach ($arPropsDefault as $code => $array){
					if (isset($array["VALUE"]["TEXT"])){
						$regionDefault[$code] = $array["VALUE"]["TEXT"];
					}
					if ($array["VALUE"]){
						$regionDefault[$code] = $array["VALUE"];
					}
				}

				$_SESSION["VREGIONS_DEFAULT"] = $regionDefault;
			}
		}else{ // chtoby lishnij raz ne obrashchat'sya k bd
			$regionDefault = $_SESSION["VREGIONS_DEFAULT"];
		}
		// vprint($regionDefault);
		// .sobiraem dannye dlya regiona po umolchaniyu

		// sobiraem informaciyu pro region
		if ((strlen($regionCode) && ($regionCode != $_SESSION["VREGIONS_REGION"]["CODE"])) || !$useSesionCache){ // esli nado sobirat' informaciyu pro region
			$region = Array();
			$res = CIBlockElement::GetList(Array(), Array("IBLOCK_ID" => $IBLOCK_ID, "CODE" => $regionCode), false, false, Array());
			if ($ob = $res->GetNextElement()){
				$arFields = $ob->GetFields();
				$arProps = $ob->GetProperties();
				$region["ID"] = $arFields["ID"];
				$region["NAME"] = $arFields["NAME"];
				$region["CODE"] = $arFields["CODE"];
				foreach ($arProps as $code => $array){
					if (isset($array["VALUE"]["TEXT"])){
						$region[$code] = $array["VALUE"]["TEXT"];
					}
					if ($array["VALUE"]){
						$region[$code] = $array["VALUE"];
					}
				}

				$_SESSION["VREGIONS_REGION"] = $region;

			}else{ // esli net takogo goroda perevodim na osnovnoj host i pokazyvaem informaciyu defoltnogo regiona
				$_SESSION["VREGIONS_REGION"] = $regionDefault;
				if ($handleErrors){
					if (strlen($errorPage)){
						header("Location: ".$errorPage);
					}else{
						header("Location: http://".$host);
					}
				}else{
					// esli ne nado obrabatyvat' oshibki, to nichego ne delaem
				}
			}
		}else{ // esli chto-to poshlo ne tak, to beryom defoltnye dannye
			if (!strlen($regionCode)){
				$_SESSION["VREGIONS_REGION"] = $regionDefault;
			}
		}
		// .sobiraem informaciyu pro region

		// rabota s yazykom
		// stavim nuzhnyj yazyk iz svojstva
		if ($_SESSION["VREGIONS_REGION"][$langProp]){
			Localization\Loc::setCurrentLang($_SESSION["VREGIONS_REGION"][$langProp]);
		}

		// podklyuchaem js-niki
		// $APPLICATION->AddHeadScript("http://maps.google.com/maps/api/js?sensor=true"); // todo?
		// $APPLICATION->AddHeadScript("/bitrix/js/vregions/geo.js");
	}
}

?>