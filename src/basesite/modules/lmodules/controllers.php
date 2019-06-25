<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
* Controllers
*
* @package HostCMS
* @version 6.x
* @author Dmitrii Lipatov
* @copyright © 2018
*/

class Shop_Controller_Show_L extends Shop_Controller_Show
{
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct($oShop);

		/*Если админ - то выводим скрытые элементы*/
			if (Core_Auth::logged() and defined('SITE_AUTH_SHOW_NOACTIVE') and SITE_AUTH_SHOW_NOACTIVE)
			{
				$this->itemsActivity = 'all';
				$this->groupsActivity = 'all';
			}

		/*Изменяем настройку полей по умолчанию*/
			$this->warehousesItems = $oShop->reserve==1;	//Остаток на каждом складе

			$this->tags = FALSE;					//Метки
			$this->groupsPropertiesList = FALSE; 	//Лист доп.св-тв групп
			$this->viewed = FALSE; 					//Просмотренные
			//$this->specialprices = TRUE; 			//Специальные цены
			//$this->filterShortcuts = TRUE; 		//Фильтровать по ярлыкам

			$this->cart = TRUE; 					//Товары в корзине
			//$this->comparing = FALSE; 			//Товары в сравнении
			//$this->favorite = FALSE; 				//Избранное

		/*Запрет полей*/
			$this->itemsForbiddenTags = array('text','description','path','image_large','image_large_width','image_large_height','guid','seo_title','seo_description','seo_keywords');
			$this->groupsForbiddenTags = array('description','path','image_large','image_large_width','image_large_height','guid','seo_title','seo_description','seo_keywords');

		/*Поля производителей*/
            $oShop_Producers = $oShop->Shop_Producers;
            $oShop_Producers->queryBuilder()
                ->select('shop_producers.id')
                ->select('shop_producers.name')
                ->select('shop_producers.shop_id')
                ->distinct()
                ->join('shop_items', 'shop_items.shop_producer_id', '=', 'shop_producers.id')
                //->where('shop_items.shop_group_id', '=', $group)
                ->where('shop_items.deleted', '=', 0);
            $aShop_Producers = $oShop_Producers->findAll();
	}


	public function show()
	{
		/*Добавляем массивы с id товаров в корзине/сравнении/избранном*/
			if ($this->cart)	//Корзина
			{
				self::_cartArrayId($this, 'items_in_cart');
				$this->cart = FALSE;
			}
			if ($this->comparing)	//Сравнение
			{
				self::_sessionArrayId($this, 'hostcmsCompare', 'comparing');
				$this->comparing = FALSE;
			}
			if ($this->favorite)	//Избранное
			{
				self::_sessionArrayId($this, 'hostcmsFavorite', 'favorite');
				$this->favorite = FALSE;
			}

		parent::show();	
	}


    /**
    * Получает массив с id товаров из КОРЗИНЫ
    *
    * Пример вызова: self::_cartArrayId($this, $name);
    *
    * $Structure_Controller_Show - Контроллер меню
    * $name - Имя переменной xml
    */
    static protected function _cartArrayId($controller, $name)
    { 
    	$shop_id = $controller->getEntity()->id;

		$aShop_Cart = Shop_Cart_Controller::instance()->getAll(Core_Entity::factory('Shop', $shop_id));

		$xml_array = array();

		foreach ($aShop_Cart as $oShop_Cart)
		{
			$xml_array[] = $oShop_Cart->shop_item_id;
		}

        //Создаем узел в xml
        $oSessionXmlEntity = Core::factory('Core_Xml_Entity')->name($name);

        //Циклом заносим значения в xml
        foreach ($xml_array as $id)
        {
            $oSessionXmlEntity->addEntity(Core::factory('Core_Xml_Entity')->name('shop_item')->addAttribute('id',$id));
        }

        $controller->addEntity($oSessionXmlEntity);
    }


    /**
    * Получает массив с id товаров из переменной $_SESSION
    *
    * Пример вызова: self::_sessionArrayId($this, 'hostcmsCompare', 'compare');
    *
    * $Structure_Controller_Show - Контроллер меню
    * $session_name - Имя переменной сессии
    * $name - Имя переменной xml
    */
    static protected function _sessionArrayId($controller, $session_name, $name)
    { 
    	$shop_id = $controller->getEntity()->id;
        if (isset($_SESSION[$session_name][$shop_id]) && count($_SESSION[$session_name][$shop_id]))
        {
            $xml_array = array();

            if (isset($_SESSION[$session_name][$shop_id][0]))
            {    
                $xml_array = $_SESSION[$session_name][$shop_id];
            }
            else
            {
                foreach ($_SESSION[$session_name][$shop_id] as $id => $array)
                {
                    $xml_array[] = $id;
                }
            }

            //Создаем узел в xml
            $oSessionXmlEntity = Core::factory('Core_Xml_Entity')->name($name);

            //Циклом заносим значения в xml
            foreach ($xml_array as $id)
            {
                $oSessionXmlEntity->addEntity(Core::factory('Core_Xml_Entity')->name('shop_item')->addAttribute('id',$id));
            }

            $controller->addEntity($oSessionXmlEntity);
        }
    }

}




class Informationsystem_Controller_Show_L extends Informationsystem_Controller_Show
{
	public function __construct(Informationsystem_Model $oInformationsystem)
	{
		parent::__construct($oInformationsystem);

		/*Если админ - то выводим скрытые элементы*/
			if (Core_Auth::logged() and defined('SITE_AUTH_SHOW_NOACTIVE') and SITE_AUTH_SHOW_NOACTIVE)
			{
				$this->itemsActivity = 'all';
				$this->groupsActivity = 'all';
			}
	}


	/*limit и offset для групп*/
		protected $_groupsLimit = NULL;
		protected $_groupsOffset = NULL;
		protected $_groupsTotal = NULL;


		public function groupsLimit($groupsLimit) //Метод $Informationsystem_Controller_Show->groupsLimit();
		{
			$this->_groupsLimit = intval($groupsLimit);
			$this->addCacheSignature('groupsLimit=' . $this->_groupsLimit);
			return $this;
		}
		public function groupsOffset($groupsOffset) //Метод $Informationsystem_Controller_Show->groupsOffset();
		{
			$this->_groupsOffset = intval($groupsOffset);
			$this->addCacheSignature('groupsOffset=' . $this->_groupsOffset);
			return $this;
		}
		public function groupsTotal($groupsTotal) //Метод $Informationsystem_Controller_Show->groupsTotal(); //Если заранее известно общее кол-во групп
		{
			$this->_groupsTotal = intval($groupsTotal);
			return $this;
		}


		public function show()
		{
			//Если передан groupsLimit
				if ($this->_groupsLimit)
				{
					//Проверяем страницу пагинации /page-N/
						$this->addEntity(Core::factory('Core_Xml_Entity')->name('groupsPage')->value($this->page));
						if ($this->page)
						{
							$this->groupsOffset($this->_groupsLimit*$this->page);
							$this->page = 0;
						}

					$this->informationsystemGroups()->queryBuilder()->limit($this->_groupsLimit);
					$this->addEntity(Core::factory('Core_Xml_Entity')->name('groupsLimit')->value($this->_groupsLimit));

					//Общее кол-во групп
						if (!$this->_groupsTotal)
						{
							$oCore_QueryBuilder_Select = Core_QueryBuilder::select()
								->select(array('COUNT(*)', 'count'))
								->from('informationsystem_groups')
								->where('informationsystem_id', '=', $this->_entity->id)
								->where('indexing', '=', 1)
								->where('deleted', '=', 0)
								->where('parent_id', '=', $this->group);

							if ($this->groupsActivity != 'all')
							{
								$oCore_QueryBuilder_Select->where('active', '=', 1);
							}

							$aRow=$oCore_QueryBuilder_Select->execute()->asAssoc()->result();
							$this->_groupsTotal = $aRow[0]['count'];
						}

						$this->addEntity(Core::factory('Core_Xml_Entity')->name('groupsTotal')->value($this->_groupsTotal));

					//Если передан groupsOffset
						if ($this->_groupsOffset)
						{
							$this->informationsystemGroups()->queryBuilder()->offset($this->_groupsOffset);
							$this->addEntity(Core::factory('Core_Xml_Entity')->name('groupsOffset')->value($this->_groupsOffset));
						}


					if ($this->_groupsTotal > $this->_groupsOffset)
					{
						return parent::show();
					}
					else
					{
						$this->error404();
					}
				}
				else
				{
					return parent::show();
				}
		}
}