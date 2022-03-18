<?php

namespace Aristov\VRegions;

use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class YML
{
    public static function makeRegionalYml(
        $ymlPath,
        $outputDirPath,
        $regionCode,
        $priceCode,
        $siteID = false,
        $xml = null,
        $noCache = false,
        $countDiscounts = true
    ) {
        static::log('Start '.$regionCode, $siteID);

        $answer = [
            'message' => '',
            'success' => false,
        ];

        // забираем xml
        if (!$xml) {
            $xml = simplexml_load_file($_SERVER['DOCUMENT_ROOT'].$ymlPath);
        }
        if (!$xml) {
            $answer['message'] = 'Cannot read '.$ymlPath.' file';

            return $answer;
        }

        static::log('Got xml');

        // получаем адрес сайта, который используетс€ в выгрузке
        $siteAddressForReplace = (string)$xml->shop->url;

        $shopUrl = \Aristov\VRegions\Tools::generateRegionLink($regionCode);
        $currency = \CCurrency::GetBaseCurrency();

        $priceID = false;
        if ($priceCode) {
            // получаем id цены по коду
            $groupIterator = \Bitrix\Catalog\GroupTable::getList(
                array(
                    "select" => array("ID", "NAME", "BASE", "SORT", "XML_ID"),
                    "order"  => array("SORT" => "ASC", "ID" => "ASC"),
                    "filter" => array("NAME" => $priceCode),
                    "limit"  => 1,
                )
            );
            $priceID = $groupIterator->fetchAll()[0]["ID"];
        }

        static::log('Got region info '.$regionCode);

        // замен€ем общую информацию
        $xml->shop->url = $shopUrl;

        $offersCount = 0;
        // замен€ем теги у товаров
        /** @var SimpleXMLElement $offer */
        foreach ($xml->shop->offers->offer as $offer) {
            $id = (string)$offer['id'];
            if (!$id) {
                continue;
            }

            $offer->url = str_replace(
                $siteAddressForReplace,
                $shopUrl,
                (string)$offer->url
            );
            $offer->picture = str_replace(
                $siteAddressForReplace,
                $shopUrl,
                (string)$offer->picture
            );

            if ($priceID) {

                // todo на 5000-ах получений цен скрипт падает, если учитывать скидки
                if ($noCache) {
                    $priceArr = static::getRegionalPrice($id, $currency, $priceID, $siteID, $countDiscounts);
                } else {
                    $priceArr = static::cachedGetOptimalPrice(
                        $id,
                        $priceID,
                        $currency,
                        $siteID,
                        $countDiscounts
                    );
                }

                $price = (double)$priceArr["RESULT_PRICE"]["DISCOUNT_PRICE"];
                $oldprice = (double)$priceArr["RESULT_PRICE"]["BASE_PRICE"];

                if ($oldprice && $price != $oldprice) {
                    $offer->price = $price;
                    $offer->oldprice = $oldprice;
                } else {
                    $offer->price = $oldprice;
                    unset($offer->oldprice);
                }
            }

            if ($offersCount % 1000 == 0) {
                static::log($offersCount, $siteID);
            }

            $offersCount++;
        }
        unset($id);
        unset($offer);

        static::log('Done with prices '.$regionCode, $siteID);

        // подготавливаем xml код
        $xml = html_entity_decode($xml->asXml());

        // записываем в файл
        $outputFilePath = $outputDirPath.$regionCode.'.xml';
        $outputFileFullPath = $_SERVER['DOCUMENT_ROOT'].$outputFilePath;
        static::log('File '.$outputFileFullPath);
        $fp = fopen($outputFileFullPath, 'w+');
        $res = fwrite($fp, $xml);
        fclose($fp);
        unset($xml);

        if ($res) {
            $answer['success'] = true;
        } else {
            $answer['message'] = 'Cannot write '.$outputFilePath.' file';
        }
        $answer['FILE_PATH'] = $outputFilePath;

        static::log('End '.$regionCode);

        return $answer;
    }

    public static function cachedGetOptimalPrice($productID, $priceID, $currency, $siteID = false, $countDiscounts = true)
    {
        $priceArr = [];

        $cache = \Bitrix\Main\Data\Cache::createInstance();
        if ($cache->initCache(86400, "av_yml_cachedGetOptimalPrice".$productID.$priceID.$siteID.$countDiscounts)) {
            $priceArr = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $priceArr = static::getRegionalPrice($productID, $currency, $priceID, $siteID, $countDiscounts);

            $cache->endDataCache($priceArr);
        }

        return $priceArr;
    }

    public static function getRegionalPrice(
        $productID,
        $currency,
        $priceID,
        $siteID = false,
        $countDiscounts = true
    ) {
        \CModule::IncludeModule('sale');
        \CModule::IncludeModule('iblock');

        $allProductPrices = \Bitrix\Catalog\PriceTable::getList(
            [
                "select" => ["PRODUCT_ID", 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY'],
                "filter" => [
                    "=PRODUCT_ID"       => $productID,
                    "=CATALOG_GROUP_ID" => $priceID,
                ],
                "order"  => ["CATALOG_GROUP_ID" => "ASC"],
                'limit'  => 1,
            ]
        )->fetchAll();
        $arPrice = $allProductPrices[0];
        $realPrice = $arPrice["PRICE"];

        $iblockID = \CIBlockElement::GetIBlockByID($productID);

        // skidka
        if ($countDiscounts) {
            $arUserGroups = array();
            $renewal = 'N';
            $arDiscountCoupons = false;
            $arDiscounts = \CCatalogDiscount::GetDiscount(
                $arPrice["PRODUCT_ID"],
                $iblockID,
                array($arPrice["CATALOG_GROUP_ID"]),
                $arUserGroups,
                $renewal,
                $siteID,
                $arDiscountCoupons
            );

            // uznaem skidku
            $discountPrice = \CCatalogProduct::CountPriceWithDiscount(
                $arPrice["PRICE"],
                $currency,
                $arDiscounts
            );
        } else {
            $discountPrice = $realPrice;
        }

        // esli drugaya valyuta
        if ($arPrice["CURRENCY"] != $currency) {
            $realPrice = \CCurrencyRates::ConvertCurrency($realPrice, $arPrice["CURRENCY"], $currency);
            $discountPrice = \CCurrencyRates::ConvertCurrency($discountPrice, $arPrice["CURRENCY"], $currency);
        }

        // nds
        $catalogProductArr = \CCatalogProduct::GetByID($productID);
        if ($catalogProductArr['VAT_ID']) {
            $vatRes = \CCatalogVat::GetByID($catalogProductArr['VAT_ID']);
            $vatArr = $vatRes->Fetch();
            if ($vatArr['RATE'] && $catalogProductArr['VAT_INCLUDED'] == 'N') {
                $discountPrice = $discountPrice * ((100 + $vatArr['RATE']) / 100);
                $realPrice = $realPrice * ((100 + $vatArr['RATE']) / 100);
            }
        }

        // okruglenie
        $discountPrice = \Bitrix\Catalog\Product\Price::roundPrice(
            $priceID,
            $discountPrice,
            $currency
        );

        $answer = [
            'RESULT_PRICE' => array(
                'BASE_PRICE'     => roundEx($realPrice, 0),
                'DISCOUNT_PRICE' => roundEx($discountPrice, 0),
                'CURRENCY'       => $currency,
            ),
        ];

        return $answer;
    }

    public static function makeRegionalYmlAgent()
    {
        $ymlPath = \Aristov\VRegions\Tools::getModuleOption("yml_file_path_from");
        $outputDirPath = \Aristov\VRegions\Tools::getModuleOption("yml_file_path_to");
        if (!$ymlPath || !$outputDirPath) {
            return false;
        }

        $siteID = \Aristov\VRegions\Tools::getModuleOption("yml_site_id");
        $_SERVER['HTTP_HOST'] = \Aristov\VRegions\Tools::getModuleOption(
            "yml_site_address"
        ); // дл€ генерации ссылки на поддомен

        // получаем xml один раз, а не в цикле
        $xml = simplexml_load_file($_SERVER['DOCUMENT_ROOT'].$ymlPath);

        $arSelect = Array(
            'ID',
            'IBLOCK_ID',
            'CODE',
        );
        // цена обрабатываетс€, только если нужно
        $pricePropCode = \Aristov\VRegions\Tools::getModuleOption("vregions_iblock_region_price_code_prop");
        if ($pricePropCode) {
            $arSelect[] = 'PROPERTY_'.$pricePropCode;
        }

        \CModule::IncludeModule('iblock');
        $resRegions = \CIBlockElement::GetList(
            Array(
                "SORT" => "ASC",
            ),
            Array(
                'IBLOCK_ID' => \Aristov\VRegions\Tools::getModuleOption("vregions_iblock_id"),
                'ACTIVE'    => 'Y',
            ),
            false,
            false,
            $arSelect
        );
        while ($arFields = $resRegions->GetNext(true, false)) {
            $res = static::makeRegionalYml(
                $ymlPath,
                $outputDirPath,
                $arFields['CODE'],
                $arFields['PROPERTY_'.$pricePropCode.'_VALUE'],
                $siteID,
                $xml
            );

            static::log(serialize($res));
        }

        return '\Aristov\VRegions\Tools::makeRegionalYmlAgent();';
    }

    public static function log($message, $siteID = false)
    {
        if (!$siteID) {
            $siteID = Tools::getSiteIDByHost();
        }

        $outputDirPath = \Aristov\VRegions\Tools::getModuleOption("yml_file_path_to", '', false, $siteID);
        if (!$outputDirPath) {
            return false;
        }

        $filePath = $_SERVER['DOCUMENT_ROOT'].$outputDirPath.'log/'.date('Y_m_d').'.txt';
        $fp = fopen($filePath, 'a+');
        $string = date('H:i:s').' '.$message.PHP_EOL;
        fwrite($fp, $string);
        fclose($fp);
    }
}