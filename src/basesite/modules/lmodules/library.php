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

class Core_Utils
{
    /**
    * Выбирает доп.св-ва по XML-тегу
    *
    * Пример вызова: $oProperty_Values = Core_Utils::getPropertyValuesByTagName($oElem, array('color','imgs')[, TRUE]);
    *
    * $oElem - элемент, в котором ищем доп. св-ва
    * $aPropertiesName - массив XML-названий доп. св-тв / array('color','imgs')
    * $onlyId - получение массива id доп.св-тв вместо их значений 
    */
    static public function getPropertyValuesByTagName($oElem, $aPropertiesName = array(), $onlyId = FALSE)
    {
        $aCorrectObjectModels = array('Informationsystem_Item_Model', 'Informationsystem_Group_Model', 'Shop_Item_Model', 'Shop_Group_Model');
        $oClass = get_class($oElem);
        $aPropertiesId = array();
        $oId = 0;

        if (isset($oElem->informationsystem_id)) { $oId = $oElem->informationsystem_id; }   //ИС
        elseif (isset($oElem->shop_id))          { $oId = $oElem->shop_id; }                //ИМ

        if( count($aPropertiesName)>0 && in_array($oClass, $aCorrectObjectModels) && $oId>0 ) {

            $aProperties = Core_Entity::factory(strstr($oClass, '_Model', true).'_Property_List', $oId)
                ->Properties
                ->findAll();

            foreach ($aProperties as $oProperty)
            {
                if (in_array($oProperty->tag_name, $aPropertiesName)) $aPropertiesId[] = $oProperty->id;
            }

            if ($onlyId)
            {
                return $aPropertiesId;
            }
            else
            {
                return $oElem->getPropertyValues(TRUE, $aPropertiesId);
            }
        }
        else
        {
            return null;
        }
    }


    /**
    * Картинка для шаринга и индексация ИМ и ИС, смена шаблона для групп и элементов
    *
    * Пример вызова: Core_Utils::setSettingsPage($oElem);
    *
    * $oElem - элемент, в котором ищем доп. св-ва
    */
    static public function setSettingsPage($oElem)
    {
        $aCorrectObjectModels = array('Informationsystem_Item_Model', 'Informationsystem_Group_Model', 'Shop_Item_Model', 'Shop_Group_Model');
        $oClass = get_class($oElem);
        if ( in_array($oClass, $aCorrectObjectModels) ) {
            /*Другой Макет для ИЭ*/
                if (Core_Array::get(Core_Page::instance()->libParams, 'item-template')>0)
                {
                    Core_Page::instance()->template( Core_Entity::factory('Template', Core_Array::get(Core_Page::instance()->libParams, 'item-template')) );
                }

            /*Картинка для шаринга*/
                if ($oElem->image_large != '')
                {
                    Core_Registry::instance()->set('image', $oElem->getLargeFileHref());
                }
                elseif ($oElem->image_small != '')
                {
                    Core_Registry::instance()->set('image', $oElem->getSmallFileHref());
                }

            /*id для меню*/
                if ( in_array($oClass, array('Informationsystem_Group_Model', 'Shop_Group_Model')) )
                {
                    Core_Registry::instance()->set('current_informationsystem_id', 'g'.$oElem->id);
                }
                else
                {
                    Core_Registry::instance()->set('current_informationsystem_id', 'e'.$oElem->id);
                }

            /*Индексация и активность*/
                $bActive = $bIndex = true;
                //Проверяем на активность
                    if ($oElem->active == 0)
                    {
                        Core_Registry::instance()->set('bNoactive', true);
                        $bActive = false;
                    }
                    elseif (!(Core_Auth::logged() and defined('SITE_AUTH_SHOW_NOACTIVE') and SITE_AUTH_SHOW_NOACTIVE))
                    {
                        $bActive = false;
                    }
                //Проверяем на индексацию
                    if ($oElem->indexing == 0)
                    {
                        Core_Registry::instance()->set('bNoindex', true);
                        $bIndex = false;
                    }
                //Если активно или индексируется - проверяем родителей
                    if ($bActive || $bIndex)
                    {
                        switch ($oClass)
                        {
                            case 'Informationsystem_Item_Model':
                                $oGroup = $oElem->informationsystem_group_id>0 ? $oElem->informationsystem_group : false;
                                break;
                            case 'Shop_Item_Model':
                                $oGroup = $oElem->shop_group_id>0 ? $oElem->shop_group : false;
                                break;
                            default:
                                $oGroup = $oElem->getParent();
                                break;
                        }

                        if ($oGroup)
                        {
                            do
                            {
                                if ($bActive and $oGroup->active == 0)
                                {
                                    Core_Registry::instance()->set('bNoactive', true);
                                    $bActive = false;
                                }
                                if ($bIndex and $oGroup->indexing == 0)
                                {
                                    Core_Registry::instance()->set('bNoindex', true);
                                    $bIndex = false;
                                }
                                if (!$bActive && !$bIndex)
                                {
                                    break;
                                }
                            }
                            while($oGroup = $oGroup->getParent());
                        }
                    }

            return true;
        }
        else
        {
            return null;
        }
    }


    /**
    * Добавляет инфоэлементы в меню
    *
    * Пример вызова: Core_Utils::menuAddItems($Structure_Controller_Show, FALSE, 27[1,11,1,'Другое']);
    *
    * $Structure_Controller_Show - Контроллер меню
    * $thisShop - TRUE = ИМ / FALSE = ИС
    * $ShopIS_id - id ИС / ИМ
    * $level - макс. уровень вложенности
    * $limit - лимит ПП
    * $type - 0=группы+элементы, 1=группы, 2=элементы
    * $other - название ссылки на общий раздел
    */
    static public function menuAddItems($Structure_Controller_Show, $thisShop = FALSE, $ShopIS_id = 3, $level = 0, $limit = 0, $type = 0, $other = '')
    {
        $link_now = str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);

        $oMenuXmlEntity = Core::factory('Core_Xml_Entity')->name('shopis')->addAttribute('id', $ShopIS_id);
        $oShopIS = Core_Entity::factory($thisShop ? 'Shop' : 'Informationsystem', $ShopIS_id);

        //Если url-идентификатор или эл-ты не индексируются - то выходим
            if ($oShopIS->Structure->changefreq == 0 || $oShopIS->url_type == 0)
            {
                return $oMenuXmlEntity;
            }
            
        //Получаем данные о ИС    
            $structure_url = $oShopIS->Structure->getPath();    //url
            $structure_id = $oShopIS->Structure->id; //id пункта меню
            if ($oShopIS->Structure->changefreq == 2) $type = 1;  //Индексировать только группы
            $hide_items = $oShopIS->apply_keywords_automatically==0; //Запретить добавление элементов в корне
            $hide_groups = $oShopIS->apply_tags_automatically==0;    //Запретить добавление групп
            
        //Выбока данных
            $aParents = array(0);                   //id родителей
            $aParentsUrl = array($structure_url);   //url родителей

            if ($type == 2) $level = 1;
            elseif ($level==0) $level = 99;

        do
        {
            $oCore_ShopIS_Groups = $oCore_ShopIS_Items = array();

        //Если не надо отображать эл-ты
            $hide_items_now = $aParents[0] == 0 && $hide_items;

        //Группы
            if ( $type!=2 && ($hide_items_now || !$hide_groups) )
            {
                $oCore_QueryBuilder_Select_Groups = Core_QueryBuilder::select()
                    ->select('name')
                    ->select('path')
                    ->select('id')
                    ->select('parent_id')
                    ->where($thisShop ? 'shop_id' : 'informationsystem_id', '=', $ShopIS_id)
                    ->where('active', '=', 1)
                    ->where('indexing', '=', 1)
                    ->where('deleted', '=', 0)
                    ->orderBy('sorting', 'ASC')

                    ->from($thisShop ? 'shop_groups' : 'informationsystem_groups')
                    ->where('parent_id', 'IN', $aParents);

                    if ($limit>0) $oCore_QueryBuilder_Select_Groups->limit($limit);

                    $oCore_ShopIS_Groups = $oCore_QueryBuilder_Select_Groups->execute()->asAssoc()->result();
            }

        //Элементы
            if ( $type!=1 && !$hide_items_now )
            {            
            $oCore_QueryBuilder_Select_Items = Core_QueryBuilder::select()
                ->select('name')
                ->select('path')
                ->select('id')
                ->select($thisShop ? 'shop_group_id' : 'informationsystem_group_id')
                ->where($thisShop ? 'shop_id' : 'informationsystem_id', '=', $ShopIS_id)
                ->where('active', '=', 1)
                ->where('indexing', '=', 1)
                ->where('deleted', '=', 0)
                ->orderBy('sorting', 'ASC')

                ->from($thisShop ? 'shop_items' : 'informationsystem_items')
                ->where($thisShop ? 'shop_group_id' : 'informationsystem_group_id', 'IN', $aParents);

                if ($limit>0) $oCore_QueryBuilder_Select_Items->limit($limit);

                $oCore_ShopIS_Items = $oCore_QueryBuilder_Select_Items->execute()->asAssoc()->result();
            }
        
        //Заполнение XML
            $oCore_ShopIS = array_merge($oCore_ShopIS_Groups, $oCore_ShopIS_Items);

            if (count($oCore_ShopIS) == 0)   //Если нет дочерних элементов
            {
                $level = 0;
            }
            else
            {
                $aParents = $aParentsUrl_new = array();
                $level--;
                foreach ($oCore_ShopIS as $aCore_ShopIS)
                {
                    //Если группа
                        $Group = isset($aCore_ShopIS['parent_id']);
                 
                    //Получаем id элемента
                        $id = $Group ? 'g' : 'e';
                        $id.= $aCore_ShopIS['id'];

                    //Получаем parent_id и url текущего пункта
                        $per_type = $Group 
                            ? 'parent_id' 
                            : ($thisShop 
                                ? 'shop_group_id' 
                                : 'informationsystem_group_id'
                                );

                        $parent_id = $aCore_ShopIS[$per_type];

                        $link = $aParentsUrl[$parent_id].$aCore_ShopIS['path'].'/';
                        $parent_id = $parent_id == 0 ? $structure_id : 'g'.$parent_id;

                    //Заполняем массив родителей для следующей итерации
                        if ($Group && $level>0)
                        {
                            $aParents[] = $aCore_ShopIS['id'];
                            $aParentsUrl_new[$aCore_ShopIS['id']] = $link;
                        }

                    $nodeName = $thisShop ? 'shop' : 'informationsystem';
                    $nodeName .= $Group ? '_group' : '_item';

                    $oXmlEntity = Core::factory('Core_Xml_Entity')->name($nodeName);
                    $oXmlEntity->addEntity(Core::factory('Core_Xml_Entity')->name('parent_id')->value($parent_id))
                               ->addEntity(Core::factory('Core_Xml_Entity')->name('name')->value($aCore_ShopIS['name']))
                               ->addEntity(Core::factory('Core_Xml_Entity')->name('link')->value($link))
                               ->addAttribute('id', $id)
                               ;

                    if ($link_now == $link) { $oXmlEntity->addEntity(Core::factory('Core_Xml_Entity')->name('active')->value(1)); }
                    elseif (strpos('.'.$link_now, $link) == 1) { $oXmlEntity->addEntity(Core::factory('Core_Xml_Entity')->name('subactive')->value(1)); }

                    $oMenuXmlEntity->addEntity($oXmlEntity);
                }
            }

            if (count($aParents) == 0) $level = 0;
            else $aParentsUrl = $aParentsUrl_new;

        }
        while($level>0);


        //Если указана ссылка на общий раздел
            if ($other != '')
            {
                $oXmlEntity = Core::factory('Core_Xml_Entity')->name('other');
                $oXmlEntity->addEntity(Core::factory('Core_Xml_Entity')->name('parent_id')->value($structure_id))
                           ->addEntity(Core::factory('Core_Xml_Entity')->name('name')->value($other))
                           ->addEntity(Core::factory('Core_Xml_Entity')->name('link')->value($structure_url.'/'))
                           ->addAttribute('id', -1)
                           ;
                $oMenuXmlEntity->addEntity($oXmlEntity);            
            }

        $Structure_Controller_Show->addEntity($oMenuXmlEntity);
    }


    /**
    * Добавляет количество товаров из переменной в дополнительное меню
    *
    * Пример вызова: Core_Utils::shopCountMenu($Structure_Controller_Show, 'cart', 3);
    *
    * $Structure_Controller_Show - Контроллер меню
    * $name - Имя переменной xml
    * $shop_id - id ИМ
    */
    static public function shopCountMenu($Structure_Controller_Show, $name, $shop_id = 3)
    {
        $count = 0;

        if ($name=='cart')
        {
            $oShop = Core_Entity::factory('Shop', $shop_id);
            $aShop_Cart = Shop_Cart_Controller::instance()->getAll($oShop);

            foreach ($aShop_Cart as $oShop_Cart)
            {
                if ($oShop->reserve==0 || $oShop_Cart->Shop_Item->getRest() > 0)
                {
                    $oShop_Cart->postpone==0 && $count += $oShop_Cart->quantity;
                }
            }
        }

        $count > 0 && $Structure_Controller_Show->addEntity(
            Core::factory('Core_Xml_Entity')->name('little_count')
                ->addAttribute('name', $name)->value($count)
            );
    }


    /**
    * Добавляет количество товаров из переменной $_SESSION в дополнительное меню
    *
    * Пример вызова: Core_Utils::sessionCountMenu($Structure_Controller_Show, 'hostcmsCompare', 'compare', 3);
    *
    * $Structure_Controller_Show - Контроллер меню
    * $session_name - Имя переменной сессии
    * $name - Имя переменной xml
    * $shop_id - id ИМ
    */
    static public function sessionCountMenu($Structure_Controller_Show, $session_name, $name, $shop_id = 3)
    { 
        if (isset($_SESSION[$session_name][$shop_id]) && count($_SESSION[$session_name][$shop_id]))
        {
            //Если может быть несколько штук 1 товара
            reset($_SESSION[$session_name][$shop_id]);
            $first = current($_SESSION[$session_name][$shop_id]);
            if (isset($first['quantity']))
            {    
                $count = 0;
                foreach ($_SESSION[$session_name][$shop_id] as $array)
                {
                    isset($array['quantity']) && (!isset($array['postpone']) || $array['postpone']==0) && $count += $array['quantity'];
                }                
            }
            else
            {
                $count = count($_SESSION[$session_name][$shop_id]);
            }

            $count > 0 && $Structure_Controller_Show->addEntity(
                Core::factory('Core_Xml_Entity')->name('little_count')
                    ->addAttribute('name', $name)->value($count)
                );
        }
    }


    /**
    * Формирует пагинацию для head
    *
    * Пример вызова: Core_Utils::setPagination($Informationsystem_Controller_Show);
    *
    * $controller - контроллер ИМ, или ИС
    */
    static public function setPagination($controller)
    {
        //Номер текущей страницы в пагинации
            $links=1;
            $controller->page && $links=$controller->page+1;

        $headPagination='';
        $oEntity = $controller->getEntity();
        $url = $oEntity->Structure->getPath(); //url

        //Ссылка на предыдущую страницу
            if ($links>2)
            {
                $headPagination.='<link rel="prev" href="'.$url.'page-'.($links-1).'/">';
            }
            elseif ($links>1)
            {
                //Если текущая страница 2 - то в качестве предыдущей выводим главную страницу ИС
                $headPagination.='<link rel="prev" href="'.$url.'">';
            }
        
        //Ссылка на последнюю страницу
            $itemsCount = get_class($oEntity) == 'Informationsystem_Model'
                ? count($oEntity->informationsystem_items->findAll())
                : count($oEntity->shop_items->findAll());

            if ($controller->offset+$controller->limit<$itemsCount)
            { 
                $headPagination.='<link rel="next" href="'.$url.'page-'.($links+1).'/">';
            }

        Core_Registry::instance()->set('headPagination', $headPagination); //Передаем значение в шаблон
    }


    /**
    * Возвращает тип устройства (phone/pad/desktop)
    *
    * Пример вызова: Core_Utils::mobileDetect();
    */
    static public function mobileDetect()
    {
        //Кэшируем
            if (Core_Registry::instance()->get('mobileDetect')) {return Core_Registry::instance()->get('mobileDetect');}

        require_once CMS_FOLDER.'modules/lmodules/Mobile_Detect.php';
        $detect = new Mobile_Detect;

        $result = 'desktop';
        if ($detect->isTablet()) $result = 'pad';
        elseif ($detect->isMobile()) $result = 'phone';

        //Кэшируем
            Core_Registry::instance()->set('mobileDetect', $result);

        return $result;
    }
}