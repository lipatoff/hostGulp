<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
* Observer
*
* @package HostCMS
* @version 6.x
* @author Dmitrii Lipatov
* @copyright © 2018
*/

class Lmodules_Shop
{

    /*Выборка связанных товаров*/
    static public function onBeforeAddAssociatedEntity($controller, $args)
    {
        $aShop_Item_Associated = $args[0];
        $aShop_Item_Associated->showXmlProperties(FALSE); //Доп.св-ва товаров 
            //->showXmlComments($this->_showXmlComments)
            //->showXmlAssociatedItems(FALSE)
            //->showXmlModifications(FALSE)
            //->showXmlSpecialprices($this->_showXmlSpecialprices)
            //->showXmlTags(array('text', 'description','path','image_large','image_large_width','image_large_height','guid'))
            //->showXmlWarehousesItems($this->_showXmlWarehousesItems)
            //->showXmlSiteuser($this->_showXmlSiteuser)
        
        $aShop_Item_Associated->addForbiddenTags(array('text', 'description','path','image_large','image_large_width','image_large_height','guid'));

    }

}



