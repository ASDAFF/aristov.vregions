<?
namespace Aristov\Vregions\EventHandlers;

class OnGetOptimalPriceHandler{
	static $MODULE_ID = "aristov.vregions";

	public static function handler(
		$productID,
		$quantity = 1,
		$arUserGroups = array(),
		$renewal = "N",
		$arPrices = array(),
		$siteID = false,
		$arDiscountCoupons = false
	){
		return \AristovVregionsHandlersHelper::onGetOptimalPriceHandler(
			$productID,
			$quantity,
			$arUserGroups,
			$renewal,
			$arPrices,
			$siteID,
			$arDiscountCoupons
		);
	}
}

?>