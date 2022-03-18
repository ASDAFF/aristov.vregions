<?

namespace Aristov\Vregions\EventHandlers;

class OnSaleComponentOrderPropertiesHandler{

	static $MODULE_ID = "aristov.vregions";

	public static function handler(&$arUserResult, $request, &$arParams, &$arResult){
		$stopEvent = (\Aristov\VRegions\Tools::getModuleOption("vregions_php_dont_substitute_location_at_order") == "Y") ? 1 : 0;
		if (!$stopEvent){
			// podstanovka regiona na stranice oformleniya zakaza
			if ($_SESSION["VREGIONS_IM_LOCATION"]["LOCATION_CODE"]){
				$db_props = \CSaleOrderProps::GetList(
					array("SORT" => "ASC"),
					array(),
					false,
					false,
					array()
				);
				while ($prop = $db_props->GetNext()){
					if ($prop["IS_LOCATION"] == 'Y'){
						if (!$request["ORDER_PROP_".$prop["ID"]]){
							$arUserResult['ORDER_PROP'][$prop["ID"]] = $_SESSION["VREGIONS_IM_LOCATION"]["LOCATION_CODE"];
						}
					}
				}
			}
		}
	}
}

?>