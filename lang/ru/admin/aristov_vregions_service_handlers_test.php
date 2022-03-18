<?
$MESS["TITLE"] = 'Тест обработчиков событий';
$MESS["HANDLER_ON_EVENT"] = 'Обработчик на событие';
$MESS["ISSET"] = 'есть';
$MESS["NOT_ISSET"] = 'нет';

$MESS['EXPLANATION_MAIN_ONPROLOG'] = 'Если нет этого обработчика, то не будут работать ни геолокация, ни подстановка переменных, ни разделение товаров, и все остальные важные вещи';
$MESS['EXPLANATION_MAIN_ONEPILOG'] = 'Если нет этого обработчика, то не будет работать подстановка переменных в метаданные';
$MESS['EXPLANATION_CATALOG_ONGETOPTIMALPRICE'] = 'Если нет этого обработчика, то не будет работать разделение цены при покупке';
$MESS['EXPLANATION_SALE_ONSALECOMPONENTORDERPROPERTIES'] = 'Если нет этого обработчика, то не будет работать подстановка местоположения на странице заказа';
$MESS['EXPLANATION_MAIN_ONENDBUFFERCONTENT'] = 'Если нет этого обработчика, то не будет работать радикальный способ подстановки переменных';
$MESS['EXPLANATION_MAIN_ONBEFOREEVENTADD'] = 'Если нет этого обработчика, то не будет работать подстановка переменных в почтовые события';
$MESS['EXPLANATION_SALE_ONSALEBASKETITEMBEFORESAVED'] = 'Если нет этого обработчика, то не будет работать добавление регионального свойства для товаров в корзине';
$MESS['HOW_TO_CURE'] = 'Как лечить';
$MESS['TREATMENT_MAIN_ONPROLOG'] = "Вставьте в файл init.php строчку: <pre>AddEventHandler('main', 'OnProlog', Array('\Aristov\Vregions\EventHandlers\CvRegionsOnPageLoad', 'vRegionsMainHandler'));</pre>";
$MESS['TREATMENT_MAIN_ONEPILOG'] = "Вставьте в файл init.php строчку: <pre>AddEventHandler('main', 'OnEpilog', Array('\Aristov\Vregions\EventHandlers\OnEpilog', 'handler'));</pre>";
$MESS['TREATMENT_CATALOG_ONGETOPTIMALPRICE'] = "Вставьте в файл init.php строчку: <pre>AddEventHandler('catalog', 'OnGetOptimalPrice', Array('\Aristov\Vregions\EventHandlers\OnGetOptimalPriceHandler', 'handler'));</pre>";
$MESS['TREATMENT_SALE_ONSALECOMPONENTORDERPROPERTIES'] = "Вставьте в файл init.php строчку: <pre>AddEventHandler('sale', 'OnSaleComponentOrderProperties', Array('\Aristov\Vregions\EventHandlers\OnSaleComponentOrderPropertiesHandler', 'handler'));</pre>";
$MESS['TREATMENT_MAIN_ONENDBUFFERCONTENT'] = "Вставьте в файл init.php строчку: <pre>AddEventHandler('main', 'OnEndBufferContent', Array('\Aristov\Vregions\EventHandlers\OnEndBufferContentHandler', 'handler'));</pre>";
$MESS['TREATMENT_MAIN_ONBEFOREEVENTADD'] = "Вставьте в файл init.php строчку: <pre>AddEventHandler('main', 'OnBeforeEventAdd', Array('\Aristov\Vregions\EventHandlers\OnBeforeEventAddHandler', 'handler'));</pre>";
$MESS['TREATMENT_SALE_ONSALEBASKETITEMBEFORESAVED'] = "Вставьте в файл init.php строчку: <pre>AddEventHandler('sale', 'OnSaleBasketItemBeforeSaved', Array('\Aristov\Vregions\EventHandlers\OnSaleBasketItemBeforeSavedHandler', 'handler'));</pre>";