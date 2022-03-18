<?

namespace Aristov\Vregions\EventHandlers;

class OnEndBufferContentHandler{

    static $MODULE_ID = "aristov.vregions";

    public static function handler(&$content){

        if (!class_exists('AristovVregionsHandlersHelper')){
            return;
        }

        if (strpos($_SERVER['REQUEST_URI'], 'ajax.php') !== false){ // чтобы содержимое почтовых шаблонов подтягивалось на странице редактирования рассылки
            return;
        }

        // schitaem, chto oni ravno tol'ko v adminke, to est' tak opredeljaem publichnaja ili administrativnaja stranica u nas
        if (!defined('ADMIN_SECTION') && $_SERVER['REQUEST_METHOD'] != 'POST' && $_REQUEST['AJAX_CALL'] != 'Y'){

            if (strpos($content, '</body>') !== false){
                // vstavka komponenta
                if (\Aristov\VRegions\Tools::getModuleOption('vregions_post_component_on_pages', 'N') == 'Y'){
                    $selector        = \Aristov\VRegions\Tools::getModuleOption('header_select_selector', 'body');
                    $selectorCommand = \Aristov\VRegions\Tools::getModuleOption('header_select_selector_command', 'prepend');

                    $content = str_replace('</body>',
                        '<script>
$(document).ready(function(){
	$.ajax({
		url     : "/bitrix/components/vregions/header.select/ajax_call.php",
		data    : {},
		type    : "get",
		success : function(answer){
			$("body").append(answer);
			$("'.$selector.'").'.$selectorCommand.'($(".vr-template"));
			
			var av = new AristovVregions;
            av.getSavedRegion(
                function(savedRegion){
                    if (savedRegion){
                        av.redirectByRegionCode(savedRegion);
                    }
                    else{
                        av.isNeedLocationCheck(
                            function(answer){
                                if (answer.success){
                                    av.checkLocation(
                                        answer.method,
                                        function(answer2){
                                            if (answer2.lat && answer2.lon){
                                                av.redirectToClosestRegion(answer2.lat, answer2.lon);
                                            }
                                        }
                                    );
                                }
                            }
                        );
                    }
                }
            );
		}
	});
});
</script>
</body>'
                        , $content);

                    $content = str_replace(
                        '</head>',
                        '<link href="/bitrix/components/vregions/header.select/templates/.default/style.css" type="text/css" rel="stylesheet">
</head>',
                        $content);

                    $content = str_replace(
                        '</body>',
                        '<script type="text/javascript" src="/bitrix/components/vregions/header.select/script.js"></script>
<script type="text/javascript" src="/bitrix/components/vregions/header.select/templates/.default/script.js"></script>
				</body>',
                        $content);
                }

                \AristovVregionsHandlersHelper::onEndBufferContentHandler($content);
            }
        }
    }
}
