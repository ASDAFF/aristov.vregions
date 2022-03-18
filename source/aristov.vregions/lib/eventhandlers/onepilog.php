<?

namespace Aristov\Vregions\EventHandlers;

class OnEpilog{

    static $MODULE_ID = "aristov.vregions";

    public static function handler(){
        \CModule::IncludeModule(static::$MODULE_ID);

        if (!class_exists('AristovVregionsHandlersHelper')){
            return;
        }

        return \AristovVregionsHandlersHelper::onEpilogHandler();
    }
}

?>