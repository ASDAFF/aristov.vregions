<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Aristov\VRegions\Tools;

class CAristovVregionsDeliveryCalcComponent extends CBitrixComponent{

	public function getLocationCode(){
		if ($this->arParams['LOCATION_CODE']){
			return $this->arParams['LOCATION_CODE'];
		}

		$location = Tools::findBitrixLocationByNameMask($_SESSION['VREGIONS_REGION']['NAME']);

		if ($location){
			return $location[0]['CODE'];
		}

		return false;
	}

	public function getPersonalType(){
		if ($this->arParams['PERSON_TYPE_ID']){
			return $this->arParams['PERSON_TYPE_ID'];
		}
		if (CModule::includeModule('sale')){
			$dbPersonType = \CSalePersonType::GetList(array(
				"SORT" => "ASC",
				"NAME" => "ASC"
			), array(
				"ACTIVE" => "Y",
				"LID"    => SITE_ID
			));
			if ($arPersonType = $dbPersonType->GetNext()){
				return $arPersonType["ID"];
			}
		}

		return false;
	}

	public function getSiteID(){
		return SITE_ID ?: 's1';
	}

	public function getProductID(){
		$productID = $this->arParams['ID_TOVARA'];
		if (!CModule::includeModule('sale') || !CModule::includeModule('catalog')){
			return $productID;
		}

		$oCatalogSKU = new \CCatalogSKU();
		$arOffers = $oCatalogSKU->getOffersList(array($productID));
		if (count($arOffers)){
			foreach ($arOffers[$productID] as $arOffer){
				return $arOffer['ID'];
			}
		}

		return $productID;
	}

	public function getCurrentShipment(\Bitrix\Sale\Order $order){
		/** @var Shipment $shipment */
		foreach ($order->getShipmentCollection() as $shipment){
			if (!$shipment->isSystem())
				return $shipment;
		}

		return null;
	}

	public function calcDeliveries(){
		\Bitrix\Main\Loader::includeModule('sale');
		\Bitrix\Main\Loader::includeModule('catalog');

		$siteID = $this->getSiteID();
		$quantity = 1;
		$personalType = $this->getPersonalType();
		$locationCode = $this->arParams['LOCATION_CODE'];
		$productID = $this->getProductID();

		$order = \Bitrix\Sale\Order::create($siteID, 1);

		$basket = \Bitrix\Sale\Basket::create($siteID);

		$productPriceArr = \CCatalogProduct::GetOptimalPrice($productID);

		$productArr = \CCatalogProduct::GetByID($productID);

		$item = $basket->createItem('catalog', $productID);
		$item->setFields(array(
			'QUANTITY'               => $quantity,
			'CURRENCY'               => \Bitrix\Currency\CurrencyManager::getBaseCurrency(),
			'LID'                    => $siteID,
			'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider',
			'WEIGHT'                 => $productArr['WEIGHT'],
			'PRICE'                  => $productPriceArr['DISCOUNT_PRICE'],
		));

		$order->setBasket($basket);

		$order->setPersonTypeId($personalType);

		$shipmentCollection = $order->getShipmentCollection();
		$shipment = $shipmentCollection->createItem();
		$shipment->setFields(array(
			'CURRENCY' => $order->getCurrency()
		));

		$shipmentItemCollection = $shipment->getShipmentItemCollection();

		foreach ($order->getBasket() as $item){
			$shipmentItem = $shipmentItemCollection->createItem($item);
			$shipmentItem->setQuantity($item->getQuantity());
		}

		$propertyCollection = $order->getPropertyCollection();
		$property = $propertyCollection->getDeliveryLocation();

		$property->setValue($locationCode);

		$deliveries = \Bitrix\Sale\Delivery\Services\Manager::getRestrictedObjectsList($shipment);

		$arDeliveries = array();
		foreach ($deliveries as $key => $deliveryObj){
			$deliveryID = $deliveryObj->getId();

			if (in_array($deliveryID, $this->arParams['EXCLUDE_DELIVERIES'])){
				continue;
			}

			$clonedOrder = $order->createClone();
			/** @var Shipment $clonedShipment */
			$clonedShipment = $this->getCurrentShipment($clonedOrder);
			$clonedShipment->setField('CUSTOM_PRICE_DELIVERY', 'N');

			$calcResult = false;
			$calcOrder = false;
			$arDelivery = array();
			//$calcResult = $deliveryObj->calculate($shipment);
			//$calcOrder = $order;

			$clonedShipment->setField('DELIVERY_ID', $deliveryID);
			$clonedOrder->getShipmentCollection()->calculateDelivery();
			$calcResult = $deliveryObj->calculate($clonedShipment);
			$calcOrder = $clonedOrder;

			if ($calcResult->isSuccess()){
				$arDelivery['PRICE'] = \Bitrix\Sale\PriceMaths::roundByFormatCurrency($calcResult->getPrice(), $calcOrder->getCurrency());
				$arDelivery['PRICE_FORMATED'] = SaleFormatCurrency($arDelivery['PRICE'], $calcOrder->getCurrency());

				$currentCalcDeliveryPrice = \Bitrix\Sale\PriceMaths::roundByFormatCurrency($calcOrder->getDeliveryPrice(), $calcOrder->getCurrency());
				if ($currentCalcDeliveryPrice >= 0 && $arDelivery['PRICE'] != $currentCalcDeliveryPrice){
					$arDelivery['DELIVERY_DISCOUNT_PRICE'] = $currentCalcDeliveryPrice;
					$arDelivery['DELIVERY_DISCOUNT_PRICE_FORMATED'] = SaleFormatCurrency($arDelivery['DELIVERY_DISCOUNT_PRICE'], $calcOrder->getCurrency());
				}

				$arDelivery["PERIOD_FROM"] = intval($calcResult->getPeriodFrom());
				$arDelivery["PERIOD_TO"] = intval($calcResult->getPeriodTo());

				if (strlen($calcResult->getPeriodDescription()) > 0){
					$arDelivery['PERIOD_TEXT'] = $calcResult->getPeriodDescription();
				}

				// если нет приписки "дней", добавляем её
				if (strlen($arDelivery["PERIOD_TEXT"])){
					if (in_array(substr(trim($arDelivery["PERIOD_TEXT"]), -1, 1), Array(
						'0',
						'1',
						'2',
						'3',
						'4',
						'5',
						'6',
						'7',
						'8',
						'9'
					))){
						$arDelivery["PERIOD_TEXT"] .= ' '.$this->getDneyWord($arDelivery["PERIOD_TO"]);
					}
				}else{
					$arDelivery["PERIOD_TEXT"] = '-';
				}

				$arDelivery["NAME"] = $deliveryObj->getName();

				$arDeliveries[] = $arDelivery;
			}
		}

		return $arDeliveries;
	}

	public function getDneyWord($count){
		$lastDigit = substr($count, -1, 1);
		switch ($lastDigit){
			case 0:
				return GetMessage('DAYS0');
				break;
			case 1:
				return GetMessage('DAYS1');
				break;
			case 2:
				return GetMessage('DAYS2');
				break;
			case 3:
				return GetMessage('DAYS3');
				break;
			case 4:
				return GetMessage('DAYS4');
				break;
			case 5:
				return GetMessage('DAYS5');
				break;
			case 6:
				return GetMessage('DAYS6');
				break;
			case 7:
				return GetMessage('DAYS7');
				break;
			case 8:
				return GetMessage('DAYS8');
				break;
			case 9:
				return GetMessage('DAYS9');
				break;
		}

		return GetMessage('DAYS9');
	}

	public function getLocationsList(){
		$locations = Array();
		$ids = Array();
		if (\CModule::IncludeModule("sale")){
			$db_vars = \CSaleLocation::GetList(
				array(
					"SORT" => "ASC",
				),
				array(),
				false,
				false,
				array(
					'ID',
					'COUNTRY_ID',
					'REGION_ID',
					'CITY_ID',
					'SORT',
					'COUNTRY_NAME_ORIG',
					'COUNTRY_SHORT_NAME',
					'REGION_NAME_ORIG',
					'CITY_NAME_ORIG',
					'REGION_SHORT_NAME',
					'CITY_SHORT_NAME',
					'COUNTRY_LID',
					'COUNTRY_NAME',
					'REGION_LID',
					'CITY_LID',
					'REGION_NAME',
					'CITY_NAME',
					'LOC_DEFAULT',
					'CODE'
				)
			);
			while ($location = $db_vars->Fetch()){
				if (!in_array($location["ID"], $ids)){
					if (LANG_CHARSET != 'UTF-8'){
						$location["CITY_NAME"] = iconv(LANG_CHARSET, 'UTF-8', $location["CITY_NAME"]);
					}
					if (LANG_CHARSET != 'UTF-8'){
						$location["COUNTRY_NAME"] = iconv(LANG_CHARSET, 'UTF-8', $location["COUNTRY_NAME"]);
					}

					$locations[] = $location;
					$ids[] = $location["ID"];
				}
			}
		}

		return $locations;
	}

	public function getLocation($code){
		if (\CModule::IncludeModule("sale")){
			$db_vars = \CSaleLocation::GetList(
				array(
					"SORT" => "ASC",
				),
				array(
					'CODE'     => $code,
					'CITY_LID' => LANGUAGE_ID
				),
				false,
				false,
				array(
					'ID',
					'COUNTRY_ID',
					'REGION_ID',
					'CITY_ID',
					'SORT',
					'COUNTRY_NAME_ORIG',
					'COUNTRY_SHORT_NAME',
					'REGION_NAME_ORIG',
					'CITY_NAME_ORIG',
					'REGION_SHORT_NAME',
					'CITY_SHORT_NAME',
					'COUNTRY_LID',
					'COUNTRY_NAME',
					'REGION_LID',
					'CITY_LID',
					'REGION_NAME',
					'CITY_NAME',
					'LOC_DEFAULT',
					'CODE'
				)
			);
			if ($location = $db_vars->Fetch()){
				return $location;
			}
		}
	}

	public function executeComponent(){
		if (!\AristovVregionsHelper::isDemoEnd()){
			$this->arParams['LOCATION_CODE'] = $this->getLocationCode();
			$this->arResult['LOCATION'] = $this->getLocation($this->arParams['LOCATION_CODE']);

			// todo по факту не работает
			if ($this->arParams['DONT_INCLUDE_PRODUCT_IN_CACHE'] == 'Y'){
				unset($this->arParams['ID_TOVARA']);
				unset($this->arParams['~ID_TOVARA']);
			}

			if ($this->startResultCache(intval($this->arParams["CACHE_TIME"]))){
				$this->arResult['DELIVERIES'] = $this->calcDeliveries();

				$this->includeComponentTemplate();
			}
		}
	}
}