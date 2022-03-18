<?

namespace Aristov\Vregions\EventHandlers;

class OnBeforeEventAddHandler{

    static $MODULE_ID = "aristov.vregions";

    public static function handler(&$event, &$lid, &$arFields){
        \CModule::IncludeModule(static::$MODULE_ID);

        return \AristovVregionsHandlersHelper::onBeforeEventAddHandler($event, $lid, $arFields);
    }
}

?>