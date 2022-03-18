<?

namespace Aristov\Vregions\EventHandlers;

class OnSaleBasketItemBeforeSavedHandler{

    static $MODULE_ID = "aristov.vregions";

    public static function handler($basketItem){
        if (\Aristov\VRegions\Tools::getModuleOption('vregions_add_region_code_prop_to_basket', 'N') == 'Y'){
            $obCollection = $basketItem->getPropertyCollection();
            $arProps      = [];
            $arProps[]    = array(
                "CODE"  => 'VREGION_CODE',
                "VALUE" => $_SESSION['VREGIONS_REGION']['CODE']
            );
            $obCollection->setProperty($arProps);
        }
    }
}