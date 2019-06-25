<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
* Library
*
* @package HostCMS
* @version 6.x
* @author Dmitrii Lipatov
* @copyright © 2018
*/

class Core_Filter
{
    /**
    * Контроллер показа
    *==============================================
    * Пример вызова: Core_Filter::get($Shop_Controller_Show [, TRUE]);
    *
    * Принимает: Контроллер ИМ, true - ajax-загрузка
    * Возвращает: Контроллер ИМ
    */
    static public function get($Shop_Controller_Show, $ajax = FALSE)
    {
$a=microtime(true);

        if (self::noempty($Shop_Controller_Show))
        {
            $href=$_SERVER['REQUEST_URI'];
            $filter='';

            if (strpos($href, '?')>0){
                $filter_pos=strpos($href, '?');
                $filter=substr($href, $filter_pos);
            }

            //Установлен фильтр по доп.св-ву
            $getProperty = strpos($filter, 'property_');

            if (Core_Array::getGet('price_from') 
             || Core_Array::getGet('price_to')
             || Core_Array::getGet('sorting')
             || Core_Array::getGet('producer_id')
             || $getProperty)
            {
                $Shop_Controller_Show->addEntity(Core::factory('Core_Xml_Entity')->name('filter')->value($filter));
            }

            $oShop = $Shop_Controller_Show->getEntity();
            $group = $Shop_Controller_Show->group ? intval($Shop_Controller_Show->group) : 0;

            //Создаем контроллер фильтра
            $Shop_Filter = $ajax ? false : new Shop_Controller_Show_L($oShop);

            list($Shop_Controller_Show, $Shop_Filter, $sorting) = self::show_sorting(   $Shop_Controller_Show, $Shop_Filter); //СОРТИРОВКА
            list($Shop_Controller_Show, $Shop_Filter)           = self::show_prices(    $Shop_Controller_Show, $Shop_Filter, $oShop, $sorting); //ЦЕНЫ
            list($Shop_Controller_Show, $Shop_Filter)           = self::show_properties($Shop_Controller_Show, $Shop_Filter, $oShop, $group, $getProperty); //ДОП.СВОЙСТВА
            list($Shop_Controller_Show, $Shop_Filter)           = self::show_producers( $Shop_Controller_Show, $Shop_Filter, $oShop, $group); //ПРОИЗВОДИТЕЛИ
            list($Shop_Controller_Show, $Shop_Filter)           = self::show_in_stock(  $Shop_Controller_Show, $Shop_Filter); //В НАЛИЧИИ

            if ($Shop_Filter)
            {
                $Shop_Filter
                    ->xsl('МагазинФильтр')
                    ->limit(0)
                    ->modificationsList(TRUE)
                    ->warehousesItems(FALSE)
                    //->itemsProperties(TRUE)
                    //->itemsPropertiesList(TRUE)
                    ->itemsPropertiesListJustAvailable(TRUE)    //Выводить только доступные значения доп.св-тв
                    ->groupsMode('tree')
                    ->group($group)
                    ->groupsForbiddenTags(array('description','path','image_large','image_large_width','image_large_height','image_small','image_small_width','image_small_height','guid','seo_title','seo_description','seo_keywords'))
                    ->calculateTotal(FALSE)
                    ->addMinMaxPrice()
                    ->cart(FALSE)       //Корзина
                    ->comparing(FALSE)  //Товары в сравнении
                    ->favorite(FALSE);   //Избранные товары

                Core_Registry::instance()->set('filter', $Shop_Filter);
            }
$a=round(microtime(true)-$a, 5);
if ($Shop_Filter) {echo '<div style="z-index:9999999;background-color:purple;color:#fff;position: fixed;top: 0;left: 0;">'.$a.'</div>';}
else {echo '<div style="z-index:9999999;background-color:red;color:#fff;position: fixed;top: 78px;left: 0;">'.$a.'</div>';}
        }


        return $Shop_Controller_Show;
    }


    /**
    * Проверка на актуальность фильтра
    *==============================================
    * Пример вызова: self::noempty($Shop_Controller_Show);
    *
    * Принимает: Объект ИМ
    * Возвращает: true - если фильтр нужно выводить
    */
    static public function noempty($Shop_Controller_Show)
    { 
        return (is_object($Shop_Controller_Show) && get_class($Shop_Controller_Show) == 'Shop_Controller_Show_L' && !$Shop_Controller_Show->item && $Shop_Controller_Show->group);
    }


    /**
    * Сортировка
    *==============================================
    * Пример вызова: self::show_sorting($Shop_Controller_Show, $Shop_Filter);
    *
    * Принимает: Контроллер, Фильтр
    * Возвращает: Контроллер, Фильтр, Соритровка
    */
    static public function show_sorting($Shop_Controller_Show, $Shop_Filter)
    {
        if (Core_Array::getGet('sorting'))
        {
            $sorting = Core_Array::getGet('sorting');
            $Shop_Controller_Show->addCacheSignature('sorting=' . $sorting);

            //Сортировка по названию
                if ($sorting == 'NAME_ASC' || $sorting == 'NAME_DESC')
                {
                    $Shop_Controller_Show->shopItems()->queryBuilder()->clearOrderBy()->orderBy('shop_items.name', $sorting == 'NAME_ASC' ? 'ASC' : 'DESC');
                }

            $Shop_Filter && $Shop_Filter->addEntity(Core::factory('Core_Xml_Entity')->name('sorting')->value($sorting));
        }
        else
        {
            $sorting = '';
        }

        return array($Shop_Controller_Show, $Shop_Filter, $sorting);
    }


    /**
    * Цены
    *==============================================
    * Пример вызова: self::show_prices($Shop_Controller_Show, $Shop_Filter, $oShop, $sorting);
    *
    * Принимает: Контроллер, Фильтр, Объект магазина, Сортировка
    * Возвращает: Контроллер, Фильтр
    */
    static public function show_prices($Shop_Controller_Show, $Shop_Filter, $oShop, $sorting)
    {
        $price_from = intval(Core_Array::getGet('price_from'));
        $price_to = intval(Core_Array::getGet('price_to'));

        if ($price_from || $price_to || $sorting == 'PRICE_ASC' || $sorting == 'PRICE_DESC')
        {
/*НЕ ДЛЯ ФИЛЬТРА*/ 
            // Получаем список валют магазина
            $aShop_Currencies = Core_Entity::factory('Shop_Currency')->findAll();

            $query_tax = 'IF(`shop_taxes`.`tax_is_included` IS NULL OR `shop_taxes`.`tax_is_included` = 1, 0, `shop_items`.`price` * `shop_taxes`.`rate` / 100)';
            $query_currency_switch = "`shop_items`.`price` + {$query_tax}";
            foreach ($aShop_Currencies as $oShop_Currency)
            {
                // Получаем коэффициент пересчета для каждой валюты
                $currency_coefficient = Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
                    $oShop_Currency, $oShop->Shop_Currency
                );

                $query_currency_switch = "IF (`shop_items`.`shop_currency_id` = '{$oShop_Currency->id}', IF (COUNT(`shop_discounts`.`id`), ((`shop_items`.`price` + {$query_tax}) * (1 - SUM(DISTINCT IF(`shop_discounts`.`type` = 0, `shop_discounts`.`value`, 0)) / 100)) * {$currency_coefficient} - SUM(DISTINCT IF(`shop_discounts`.`type`, `shop_discounts`.`value`, 0)), (`shop_items`.`price`) * {$currency_coefficient}), {$query_currency_switch})";
            }

            $current_date = date('Y-m-d H:i:s');
            $Shop_Controller_Show->shopItems()
                ->queryBuilder()
                ->select(array(Core_QueryBuilder::expression($query_currency_switch), 'absolute_price'))
                ->leftJoin('shop_item_discounts', 'shop_items.id', '=', 'shop_item_discounts.shop_item_id')
                ->leftJoin('shop_discounts', 'shop_item_discounts.shop_discount_id', '=', 'shop_discounts.id', array(
                    array('AND ' => array('shop_discounts.active', '=', 1)),
                    array('AND ' => array('shop_discounts.deleted', '=', 0)),
                    array('AND' => array('shop_discounts.start_datetime', '<=', $current_date)),
                    array('AND (' => array('shop_discounts.end_datetime', '>=', $current_date)),
                    array('OR' => array('shop_discounts.end_datetime', '=', '0000-00-00 00:00:00')),
                    array(')' => NULL)
                ))
                ->leftJoin('shop_taxes', 'shop_taxes.id', '=', 'shop_items.shop_tax_id')
                ->groupBy('shop_items.id');

            //От
            if ($price_from)
            {
                $Shop_Controller_Show->shopItems()->queryBuilder()->having('absolute_price', '>=', $price_from);
                $Shop_Controller_Show->addCacheSignature('price_from=' . $price_from);

                $Shop_Filter && $Shop_Filter->addEntity(Core::factory('Core_Xml_Entity')->name('price_from')->value($price_from));
            }

            //До
            if ($price_to)
            {
                $Shop_Controller_Show->shopItems()->queryBuilder()->having('absolute_price', '<=', $price_to);
                $Shop_Controller_Show->addCacheSignature('price_to=' . $price_to);

                $Shop_Filter && $Shop_Filter->addEntity(Core::factory('Core_Xml_Entity')->name('price_to')->value($price_to));
            }

            if ($sorting == 'PRICE_ASC' || $sorting == 'PRICE_DESC')
            {
                $Shop_Controller_Show->shopItems()->queryBuilder()->clearOrderBy()->orderBy('absolute_price', $sorting == 'PRICE_ASC' ? 'ASC' : 'DESC');
            }
/**/
        }


        return array($Shop_Controller_Show, $Shop_Filter);
    }


    /**
    * Дополнительные свойства
    *==============================================
    * Пример вызова: self::show_properties($Shop_Controller_Show, $Shop_Filter, $oShop, $group, $getProperty);
    *
    * Принимает: Контроллер, Фильтр, объект магазина, группа, true - есть фильтрация по доп.св-вам
    * Возвращает: Контроллер, Фильтр
    */
    static public function show_properties($Shop_Controller_Show, $Shop_Filter, $oShop, $group, $getProperty)
    {
        //if ($getProperty || $Shop_Filter)   //Если фильтруем или выводим фильтр
        if ($getProperty)   //Если фильтруем
        {
            $oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);

            $aProperties = $group>0 && is_null($Shop_Controller_Show->tag)
                ? $oShop_Item_Property_List->getPropertiesForGroup($group)
                : $oShop_Item_Property_List->Properties->findAll();

            $aTmpProperties = array();

            $havingCount = 0;

            foreach ($aProperties as $oProperty)
            {
                // Св-во может иметь несколько значений
                $aPropertiesValue = Core_Array::getGet('property_' . $oProperty->id);
            
                if ($aPropertiesValue)
                {
                    !is_array($aPropertiesValue) && $aPropertiesValue = array($aPropertiesValue);
                    $aPropertiesValue = array_map('strval', $aPropertiesValue);

                    $aTmpProperties[] = array($oProperty, $aPropertiesValue);
                    $havingCount++;
                }
                elseif (!is_null(Core_Array::getGet('property_' . $oProperty->id . '_from')) || !is_null(Core_Array::getGet('property_' . $oProperty->id . '_to')))
                {
                    $tmpFrom = $tmpTo = FALSE;
                    !is_null(Core_Array::getGet('property_' . $oProperty->id . '_from')) && $tmpFrom = Core_Array::getGet('property_' . $oProperty->id . '_from');
                    !is_null(Core_Array::getGet('property_' . $oProperty->id . '_to')) && $tmpTo = Core_Array::getGet('property_' . $oProperty->id . '_to');

                    $tmpFrom && !is_array($tmpFrom) && $tmpFrom = array($tmpFrom);
                    $tmpTo && !is_array($tmpTo) && $tmpTo = array($tmpTo);

                    // From ... to ...
                    $aTmpProperties[] = array($oProperty, array(
                        'from' => $tmpFrom && $tmpFrom[0] != ''
                            ? ($oProperty->type == 11 ? floatval($tmpFrom[0]) : intval($tmpFrom[0]))
                            : 0,
                        'to' => $tmpTo && $tmpTo[0] != ''
                            ? ($oProperty->type == 11 ? floatval($tmpTo[0]) : intval($tmpTo[0]))
                            : 0
                    ));

                    /*
                    foreach ($tmpFrom as $iKey => $sValue)
                    {
                        echo "string";
                        $to = Core_Array::get($tmpTo, $iKey);

                        $aTmpProperties[] = array($oProperty, array(
                            'from' => $sValue != ''
                                ? ($oProperty->type == 11 ? floatval($sValue) : intval($sValue))
                                : '',
                            'to' => $to != ''
                                ? ($oProperty->type == 11 ? floatval($to) : intval($to))
                                : ''
                        ));
                    }
                    */
                    $havingCount++;
                }
            }

            if (count($aTmpProperties))
            {
                $aTableNames = array();

                $Shop_Controller_Show->shopItems()->queryBuilder()
                    ->leftJoin('shop_item_properties', 'shop_items.shop_id', '=', 'shop_item_properties.shop_id')
                    ->setAnd()
                    ->open();

                foreach ($aTmpProperties as $aTmpProperty)
                {
                    list($oProperty, $aPropertyValues) = $aTmpProperty;

                    $tableName = $oProperty->createNewValue(0)->getTableName();

                    !in_array($tableName, $aTableNames) && $aTableNames[] = $tableName;

                    $Shop_Controller_Show->shopItems()->queryBuilder()
                        ->where('shop_item_properties.property_id', '=', $oProperty->id);

                    if (!isset($aPropertyValues['from']))
                    {
                        // Для строк фильтр LIKE %...%
                        if ($oProperty->type == 1)
                        {
                            foreach ($aPropertyValues as $propertyValue)
                            {
                                $Shop_Controller_Show->shopItems()->queryBuilder()
                                    ->where($tableName . '.value', 'LIKE', "%{$propertyValue}%");
                            }
                        }
                        else
                        {
                            // Checkbox
                            $oProperty->type == 7 && $aPropertyValues[0] != '' && $aPropertyValues = array(1);

                            $bCheckUnset = $oProperty->type != 7 && $oProperty->type != 3;

                            $bCheckUnset && $Shop_Controller_Show->shopItems()->queryBuilder()->open();

                            $Shop_Controller_Show->shopItems()->queryBuilder()
                                ->where(
                                    $tableName . '.value',
                                    count($aPropertyValues) == 1 ? '=' : 'IN',
                                    count($aPropertyValues) == 1 ? $aPropertyValues[0] : $aPropertyValues
                                );

                            $bCheckUnset && $Shop_Controller_Show->shopItems()->queryBuilder()
                                ->setOr()
                                ->where($tableName . '.value', 'IS', NULL)
                                ->close();
                        }

                        $Shop_Controller_Show->shopItems()->queryBuilder()->setOr();

                        foreach ($aPropertyValues as $propertyValue)
                        {
                            $Shop_Controller_Show->addCacheSignature("property{$oProperty->id}={$propertyValue}");

                            $Shop_Filter && $Shop_Filter
                                ->addEntity(Core::factory('Core_Xml_Entity')->name('property_' . $oProperty->id)->value($propertyValue));
                        }
                    }
                    else
                    {
                        $from = $to = FALSE;
                        !is_null(Core_Array::get($aPropertyValues, 'from')) && $from = trim(Core_Array::get($aPropertyValues, 'from'));
                        !is_null(Core_Array::get($aPropertyValues, 'to')) && $to = trim(Core_Array::get($aPropertyValues, 'to'));
                        if ($from == 0) $from = FALSE;
                        if ($to == 0) $to = FALSE;

                        $from && $Shop_Controller_Show->addCacheSignature("property{$oProperty->id}_from={$from}");
                        $to && $Shop_Controller_Show->addCacheSignature("property{$oProperty->id}_to={$to}");

                        $from && $Shop_Filter && $Shop_Filter->addEntity(Core::factory('Core_Xml_Entity')->name('property_' . $oProperty->id . '_from')->value($from));
                        $to && $Shop_Filter && $Shop_Filter->addEntity(Core::factory('Core_Xml_Entity')->name('property_' . $oProperty->id . '_to')->value($to));


    /*НЕ ДЛЯ ФИЛЬТРА*/ 
                        if ($oProperty->type == 3 && ($from || $to)) //Список
                        {
                            $oCore_QueryBuilder_Select_Lists = Core_QueryBuilder::select()
                                ->select('id')
                                ->from('list_items')
                                ->where('list_id', '=', $oProperty->list_id)
                                ->where('active', '=', 1)
                                ->where('deleted', '=', 0)
                                ->orderBy('id', 'ASC');

                            if ($from) $oCore_QueryBuilder_Select_Lists->where('list_items.value', '>=', $from);
                            if ($to) $oCore_QueryBuilder_Select_Lists->where('list_items.value', '<=', $to);

                            $oCore_Lists = $oCore_QueryBuilder_Select_Lists->execute()->asAssoc()->result();

                            $array = array();
                            foreach ($oCore_Lists as $oCore_List)
                            {
                                $array[] = $oCore_List['id'];
                            }

                            $Shop_Controller_Show->shopItems()->queryBuilder()
                                ->where($tableName . '.value', 'IN', $array)
                                ->setOr();
                        }
                        elseif($from || $to)
                        {
                            $from && $Shop_Controller_Show->shopItems()->queryBuilder()
                                ->open()
                                ->where($tableName . '.value', '>=', $from)
                                ->setOr()
                                ->where($tableName . '.value', 'IS', NULL)
                                ->close()
                                ->setAnd();

                            $to && $Shop_Controller_Show->shopItems()->queryBuilder()
                                ->open()
                                ->where($tableName . '.value', '<=', $to)
                                ->setOr()
                                ->where($tableName . '.value', 'IS', NULL)
                                ->close();

                            $Shop_Controller_Show->shopItems()->queryBuilder()->setOr();
                        }
    /**/
                    }
                }

    /*НЕ ДЛЯ ФИЛЬТРА*/
                $Shop_Controller_Show->shopItems()->queryBuilder()
                    ->close()
                    ->groupBy('shop_items.id');

                $havingCount > 1 && $Shop_Controller_Show->shopItems()->queryBuilder()
                    ->having(Core_Querybuilder::expression('COUNT(DISTINCT `shop_item_properties`.`property_id`)'), '=', $havingCount);

                foreach ($aTableNames as $tableName)
                {
                    $Shop_Controller_Show->shopItems()->queryBuilder()
                        ->leftJoin($tableName, 'shop_items.id', '=', $tableName . '.entity_id',
                            array(
                                array('AND' => array('shop_item_properties.property_id', '=', Core_QueryBuilder::expression($tableName . '.property_id')))
                            )
                        );
                }
    /**/
            }
        }

        return array($Shop_Controller_Show, $Shop_Filter);
    }

/*
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    static public function show_($Shop_Controller_Show, $oShop, $group)
    {
        //Количество
            $on_page = intval(Core_Array::getGet('on_page'));
            if ($on_page > 0 && $on_page < 150)
            {
                $Shop_Controller_Show->addEntity(Core::factory('Core_Xml_Entity')->name('on_page')->value($on_page));
            }
        return $Shop_Controller_Show;
    }
*/

    /**
    * Производители
    *==============================================
    * Пример вызова: self::show_producers($Shop_Controller_Show, $Shop_Filter, $oShop, $group);
    *
    * Принимает: Контроллер, Фильтр, объект магазина, группа
    * Возвращает: Контроллер, Фильтр
    */
    static public function show_producers($Shop_Controller_Show, $Shop_Filter, $oShop, $group)
    {
        $aProducersValues = Core_Array::getGet('producer_id');

        if ($aProducersValues)
        {
            !is_array($aProducersValues) && $aProducersValues = array($aProducersValues);

            $Shop_Controller_Show->shopItems()->queryBuilder()
                ->where(
                    'shop_producer_id',
                    count($aProducersValues) == 1 ? '=' : 'IN',
                    count($aProducersValues) == 1 ? $aProducersValues[0] : $aProducersValues
                );

            foreach ($aProducersValues as $producerValue)
            {
                $Shop_Controller_Show->addCacheSignature('producer_id=' . $producerValue);

                $Shop_Filter && $Shop_Filter->addEntity(Core::factory('Core_Xml_Entity')->name('producer_id')->value($producerValue));
            }
        }

        // Список производителей для фильтра
        if ($Shop_Filter)
        {
            $oProducersXmlEntity = Core::factory('Core_Xml_Entity')->name('producers');

            $Shop_Filter->addEntity($oProducersXmlEntity);

            $oShop_Producers = $oShop->Shop_Producers;
            $oShop_Producers->queryBuilder()
                ->select('shop_producers.id')
                ->select('shop_producers.name')
                ->select('shop_producers.shop_id')
                ->distinct()
                ->join('shop_items', 'shop_items.shop_producer_id', '=', 'shop_producers.id')
                ->where('shop_items.shop_group_id', '=', $group)
                ->where('shop_items.deleted', '=', 0);

            $aShop_Producers = $oShop_Producers->findAll();
            
            foreach ($aShop_Producers as $oShop_Producer)
            {
                $oProducersXmlEntity->addEntity($oShop_Producer->clearEntities());
            }
        }

        return array($Shop_Controller_Show, $Shop_Filter);
    }


    /**
    * В наличии
    *==============================================
    * Пример вызова: self::show_in_stock($Shop_Controller_Show, $Shop_Filter);
    *
    * Принимает: Контроллер, Фильтр
    * Возвращает: Контроллер, Фильтр
    */
    static public function show_in_stock($Shop_Controller_Show, $Shop_Filter)
    {
        Core_Array::getGet('in_stock') && $Shop_Controller_Show->warehouseMode('in-stock');  
        
        /* Если верхний не сработает
        Core_Array::getGet('in_stock') && $Shop_Controller_Show->shopItems()->queryBuilder()
            //->select('shop_items.*')
            ->leftJoin('shop_warehouse_items', 'shop_warehouse_items.shop_item_id', '=', 'shop_items.id')
            ->groupBy('shop_items.id')
            ->having('sum(shop_warehouse_items.count)', '>', 0);
        */    

        //Передаем в фильтр параметр
        $Shop_Filter && $Shop_Filter->addEntity(Core::factory('Core_Xml_Entity')
            ->name('in_stock')
            ->value( Core_Array::getGet('in_stock') ? 1 : 0 )
            );

        return array($Shop_Controller_Show, $Shop_Filter);
    }    

}