<?
$MESS["TITLE"]                                 = "Генерация динамической карты сайты";
$MESS["SITEMAP"]                               = "Карта сайта";
$MESS["ROBOTS_DIFFICULT"]                      = "Robots.txt (сложный путь)";
$MESS["SITEMAP_CREATED"]                       = "Карта сгенерирована";
$MESS["SITEMAP_DIDNT_CREATED"]                 = "Карта не смогла сгенерироваться";
$MESS["OLD_SITEMAP_ADDRESS"]                   = "Карта сайта для замены";
$MESS["DOMAIN"]                                = "Домен сайта указанный в карте сайта";
$MESS["DOMAIN"]                                = "Домен сайта указанный в карте сайта";
$MESS["NEW_SITEMAP_ADDRESS"]                   = "Название динамической карты сайта, которое заменит выбранную";
$MESS["CREATE"]                                = "Создать";
$MESS["MAKE_DYN"]                              = "Сделать динамическим";
$MESS["ROBOTS_CREATED"]                        = "Robots.txt изменён";
$MESS["ROBOTS_NO_ROBOTS"]                      = "Нет файла robots.txt";
$MESS["ROBOTS_CANNOT_WRITE"]                   = "Ошибка записи";
$MESS["ROBOTS_NO_CODE_FOR_PROP"]               = "У данного свойства нет кода";
$MESS["ROBOTS"]                                = "Robots.txt (простой путь)";
$MESS["ROBOTS_PROP"]                           = "Свойство для хранения robots.txt";
$MESS["PHP_IN_TXT_DOESNT_WORK"]                = "Внимание!<br>Не работает php-код в .txt-файлах. Из-за этого не будет работать динамический robots.txt.<br>Обратитесь, пожалуйста, к хостеру.";
$MESS["GENERATE_MAP_DESCRIPTION"]              = 'Подробная инструкция по ссылке <a href="http://av-promo.ru/modules/aristov.vregions/sitemap-xml-dlya-regionov.html" target="_blank">http://av-promo.ru/modules/aristov.vregions/sitemap-xml-dlya-regionov.html</a>';
$MESS["DONT_FORGET_SITEMAP_HTACCESS_HEADER"]   = 'Редиректы';
$MESS["DONT_FORGET_SITEMAP_HTACCESS"]          = 'Чтобы индексация работала правильно, не забудьте прописать в .htaccess:';
$MESS["AUTO_SITEMAP_GENERATION_HEADER"]        = 'Автоматическая генерация';
$MESS["AUTO_SITEMAP_GENERATION"]               = 'Чтобы карты сайта генерировались автоматически, запустите создание всех нужных карт сайта 1 раз здесь, а потом добавьте агента с функцией "\Aristov\VRegions\Tools::sitemapAgent();". Пример настроек агента на скриншоте:';
$MESS["AUTO_SITEMAP_GENERATION_IMG_TITLE"]     = 'Настройки агента для автоматической генерации динамических карт сайта';
$MESS["PHP_IN_TXT_DOESNT_WORK"]                = "Внимание!<br>Не работает php-код в .txt-файлах. Из-за этого не будет работать динамический robots.txt.<br>Обратитесь, пожалуйста, к хостеру или просто создавайте .php, а не .txt файл.";
$MESS["GENERATE_ROBOTS_SIMPLE_DESCRIPTION"]    = 'При отправке этой формы берётся ваш файл robots.txt и в нём заменяются все вхождения вашего адреса сайта, на специальный код, который подставляет нужный поддомен, когда это необходимо.<br><br>
То есть после нажатия на кнопку при открытии вашего сайта через поддомен в директиве "Host" будет не адрес вашего сайта, а адрес вашего сайта вместе с поддоменом.<br><br>
Более подробная инструкция по ссылке <a href="http://av-promo.ru/modules/aristov.vregions/raznyy-robots-txt-v-raznykh-regionakh.html" target="_blank">http://av-promo.ru/modules/aristov.vregions/raznyy-robots-txt-v-raznykh-regionakh.html</a>';
$MESS["GENERATE_ROBOTS_DIFFICULT_DESCRIPTION"] = 'Здесь вы указываете свойство элементов инфоблока регионов, в котором вы будете хранить полный текст robots.txt для этого поддомена. То есть можно делать абсолютно разные robots.txt для разных поддоменов.<br>
Свойство инфоблока должно быть типа "html/текст". <b>Свойство создать вам нужно самим.</b><br><br>
Пример того, что вставлять в это свойство:<br>
User-Agent: *<br>
Disallow: */index.php<br>
Disallow: /bitrix/<br>
Disallow: /*show_include_exec_time=<br>
Disallow: /*show_page_exec_time=<br>
Disallow: /*show_sql_stat=<br>
Disallow: /*bitrix_include_areas=<br>
Disallow: /*clear_cache=<br>
Disallow: /*clear_cache_session=<br>
Disallow: /*ADD_TO_COMPARE_LIST<br>
Disallow: /*ORDER_BY<br>
Disallow: /*PAGEN<br>
Disallow: /*?print=<br>
Disallow: /*&print=<br>
Disallow: /*print_course=<br>
Disallow: /*?action=<br>
Disallow: /*&action=<br>
Disallow: /*register=<br>
Disallow: /*forgot_password=<br>
Disallow: /*change_password=<br>
Disallow: /*login=<br>
Disallow: /*logout=<br>
Disallow: /*auth=<br>
Disallow: /*backurl=<br>
Disallow: /*back_url=<br>
Disallow: /*BACKURL=<br>
Disallow: /*BACK_URL=<br>
Disallow: /*back_url_admin=<br>
Disallow: /*?utm_source=<br>
Allow: /bitrix/components/<br>
Allow: /bitrix/cache/<br>
Allow: /bitrix/js/<br>
Allow: /bitrix/templates/<br>
Allow: /bitrix/panel/<br>
Host: HTTP_HOST<br>
Sitemap: http://HTTP_HOST/sitemap_000.xml<br><br>
После того как создадите и заполните свойство, выберите его в списке и нажмите на кнопку.<br><br>
Более подробная инструкция по ссылке <a href="http://av-promo.ru/modules/aristov.vregions/raznyy-robots-txt-v-raznykh-regionakh.html" target="_blank">http://av-promo.ru/modules/aristov.vregions/raznyy-robots-txt-v-raznykh-regionakh.html</a>';
$MESS["MAKE_PHP_FILE"]                         = 'Сделать не txt-файл, а php-файл';
$MESS["MAKE_PHP_FILE_DESC"]                    = 'Нужно добавить строчку "RewriteRule ^robots\.txt$ /robots.php [L]" в файл .htaccess';
$MESS["ALL_LINKS_ONLY_HTTPS"]                  = 'Использовать только https в ссылках';
$MESS["SET_TYPICAL_ROBOTS_BTN"]                = 'Задать';
$MESS["SET_TYPICAL_ROBOTS_TITLE"]              = 'Задать одинаковый robots.txt всем элементам';
$MESS["ROBOTS_CONTENT"]                        = 'Значение';
$MESS["REPLACE_HOST_ADDRESS"]                  = 'Заменять адрес сайта при выводе robots.txt';
$MESS["REPLACE_HOST_ADDRESS_DESC"]             = 'В этом случае можно будет не заменять адрес сайта в robots.txt у каждого элемента инфоблока регионов. Адрес сайта сам заменится на нужный при выводе robots.txt';