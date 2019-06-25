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

class Lmodules_Form
{

    /*Классы для полей*/
    static protected function _addClass($field, $class, $img_hide = false) //Поле, id класса, true если надо скрыть малое изображение
    {
        
        $class_text='form-group ';

        switch ($class)
        {
           case 2:  $class_text.='col-xs-6 col-sm-2'; break;
           case 4:  $class_text.='col-xs-12 col-sm-4'; break;
           case 6:  $class_text.='col-xs-12 col-sm-6'; break;
           case 8:  $class_text.='col-xs-12 col-sm-8'; break;
           case 10: $class_text.='col-xs-12 col-sm-10'; break;
           case 12: $class_text.='col-xs-12'; break;
        }

        if ($img_hide){ $class_text.=' img-hide'; }

        $field->divAttr(array('class' => $class_text));
    }


    static protected function _delSmallImg($field)     //Удаление/загрузка малых изображений при удалении/загрузке больших
    {
        //Удаление малого при удалении большого
            $largeImage = $field->largeImage;
            if (isset($largeImage['delete_onclick']) && isset($field->smallImage['delete_onclick']))
            {
                $largeImageDelAction = $field->smallImage['delete_onclick'];
                $largeImageDelAction = str_replace('return false', '', $largeImageDelAction);
                $largeImageDelAction .=  $largeImage['delete_onclick'];
                $largeImage['delete_onclick'] = $largeImageDelAction;
                $field->set('largeImage', $largeImage);
            }

        //Загрузка малого при загрузке большого
            $smallImage = $field->smallImage;
            $smallImage['path'] = NULL;
            $smallImage['create_small_image_from_large_checked'] = 1;
            $field->set('smallImage', $smallImage);
    }

    static protected function _delGroup($field)     //Скрываем выбор группы связанных ИС/ИМ, если их нет
    {
        $divAttr = $field->divAttr;
        $divAttr['class'] .= ' group-hide';
        $field->divAttr($divAttr);
    }

    static protected function _settingsProps($aProp)    //Установки для полей доп св-тв
    {
        switch (get_class($aProp))
        {
            case 'Skin_Bootstrap_Admin_Form_Entity_File': //Скрытие малых изображений
                if ($aProp->smallImage['show'] != 1)
                {
                    $smallImage = $aProp->smallImage;
                    $smallImage['show'] = 1;
                    $aProp->set('smallImage', $smallImage);
                    //$pic = 'Картинка '.substr($aProp->id, strrpos($aProp->id, '_')+1);
                    //$aProp->caption($pic);
                    
                    self::_delSmallImg($aProp);
                    self::_addClass($aProp, 12, true);
                }
                break;

            case 'Skin_Bootstrap_Admin_Form_Entity_Textarea':   //Размер поля textarea
                $aProp->rows = 10;
                break;

            case 'Skin_Bootstrap_Admin_Form_Entity_Input': //Подмена цифр на списки
                $newPropArray = false;
                /*
                //По ID св-ва
                    if (strpos($aChi[0]->name, '134')>0 && defined('TYPE_EVENT') && TYPE_EVENT)
                    {
                        $newPropArray = explode(',', TYPE_EVENT);
                    }

                //По названию св-ва
                    if ($aChi[0]->caption == 'Уровень сложности' && defined('LEVEL') && LEVEL)
                    {
                        $newPropArray = explode(',', LEVEL);
                    }
                */

                if ($newPropArray) //Заменяем на список
                {
                    for ($i=0; $i < count($newPropArray); $i++)
                    { 
                        $newPropArray[$i] = trim($newPropArray[$i]);
                    }

                    $oldField=$aChi[0];
                    //Удаление поля
                        $aChild->deleteChild(0);
                    
                    //Создание нового поля  
                        $newField = Admin_Form_Entity::factory('Select')
                            ->options($newPropArray)
                            ->name($oldField->name)
                            ->divAttr(array('class'=>'form-group col-xs-4'))
                            ->value($oldField->value)
                            ->caption($oldField->caption);

                        $aChild->add($newField);                                
                }
                else    //Оставляем не тронутым
                {
                    //$aChi[0]->divAttr(array('class'=>'form-group col-xs-4'));
                }
                break;                
        }
    }

    /*Общие правила для форм*/
    static protected function _Form_All($controller, $object, $ObjectClass) //Контроллер, объект и тип формы
    {

        /*Табы*/
            $oMainTab = $controller->getTab('main');
            $oDescriptionTab = $controller->getTab('Description');
            $oPropertyTab = $controller->getTab('Property'); //Доп. св-ва
            $oAdditionalTab = $controller->getTab('additional'); //Дополнительные

            if ($controller->issetTab('Associateds')){ $oAssociatedsTab = $controller->getTab('Associateds'); } //Сопутствующие
            if ($controller->issetTab('Seo_Templates')){ $oSeoTemplatesTab = $controller->getTab('Seo_Templates'); } //Шаблоны SEO
            
            if ($controller->issetTab('ExportImport')){ $oExportImportTab = $controller->getTab('ExportImport'); }
            elseif ($controller->issetTab('ImportExport')){ $oExportImportTab = $controller->getTab('ImportExport'); }
            elseif ($controller->issetTab('Export')){ $oExportImportTab = $controller->getTab('Export'); }

            $oTabs = $controller->getTabs(); //Все вкладки

            foreach ($oTabs as $oTab) {
                if (strtoupper($oTab->name)=='SEO') { $oSeoTab=$oTab; break; } //SEO
            }

        /*Создание новой вкладки*/
            $oAdmin_Form_Tab_Entity_L = Admin_Form_Entity::factory('Tab')
                ->name('lhome')
                ->caption('Основная');

            $controller->addTabBefore($oAdmin_Form_Tab_Entity_L, $oMainTab);
            $oHomeTab = $controller->getTab('lhome'); //l-вкладка

        /*Получение настроек конкретной ИС/ИМ*/
            $oInfoShop = false;
            switch ($ObjectClass)
            {   
                //ИС
                case 'Informationsystem_Item_Model':
                case 'Informationsystem_Group_Model':                 
                    $oInfoShop = $object->Informationsystem;
                break;

                //ИМ
                case 'Shop_Item_Model':
                case 'Shop_Group_Model':
                    $oInfoShop = $object->Shop;
                break;
            }


            if ($ObjectClass=='Shop_Group_Model' || $ObjectClass=='Informationsystem_Group_Model')
            {
                $home = !$controller->getField('parent_id')->value;   //Находимся в корне
            }
            else
            {
                $home = false;
            }


        /*Перенос основных полей с основной вкладки*/

            //Скрытие малых изображений
                $photo_hide = false;

                if (defined('HOOK_FORM_IMG_HIDE') and HOOK_FORM_IMG_HIDE)  //Вкл константа Скрытия малых изображений
                {
                    $photo_hide = ($ObjectClass=='Informationsystem_Item_Model' && $oInfoShop->typograph_default_items==1)
                               || ($ObjectClass=='Informationsystem_Group_Model' && $oInfoShop->typograph_default_groups==1)
                               || ($ObjectClass=='Shop_Item_Model' && $oInfoShop->typograph_default_items==1)
                               || ($ObjectClass=='Shop_Group_Model' && $oInfoShop->typograph_default_groups==1);
                }

            $aMoveFields = array(
                'name',
                'image',
                'path'
                );

            /*Отдельно для каждой формы*/
            switch ($ObjectClass)
            {
                //Инфоэлемент
                case 'Informationsystem_Item_Model': case 'Shop_Item_Model':
                    switch ($oInfoShop->id)
                    {
                        case 1:
                            $aMoveFields = array(
                                'path'
                                );
                        break;
                    }
                break;

                //Группа
                case 'Informationsystem_Group_Model': case 'Shop_Group_Model':
                break;
            }

            $aFields = $oMainTab->getFields();

            foreach ($aFields as $aField)
            {
                $aChilds=$aField->getChildren();
                if ((count($aChilds)>0) && isset($aChilds[0]->name) && in_array($aChilds[0]->name, $aMoveFields))
                {
                    if ($photo_hide && $aChilds[0]->name=='image') //Скрытие малого изображения и удаление его при удалении большого
                    {
                        self::_delSmallImg($aChilds[0]);                     
                        self::_addClass($aChilds[0], 12, true);
                    }

                    $oMainTab->move($aField,$oHomeTab);
                }
            }

        /*Размеры полей*/
            self::_addClass($controller->getField('path'), 12);
            self::_addClass($controller->getField('sorting'), 2);
            $description = $controller->getField('description'); $description->rows = 5;

        /*Перенос поля сортировки*/
            if ($ObjectClass!='Shop_Item_Model')
            {
                $oMainTab->moveAfter($controller->getField('sorting'), $controller->getField('path'), $oHomeTab);
            }
        
        /*Ключевое поле для переноса полей*/
            $aHomeKeyField=$controller->getField('sorting');

        /*Перенос поля id, если есть*/
            $aFields = $oAdditionalTab->getFields();
            foreach ($aFields as $aField)
            {
                $aChilds=$aField->getChildren();
                if ((count($aChilds)>0) && isset($aChilds[0]->name) && ($aChilds[0]->name=='id'))
                {
                    
                    $aMoveField = $controller->getField('id');
                    // $oAdditionalTab->moveAfter($aMoveField, $controller->getField('name'), $oHomeTab);
                    
                    $aName = $oHomeTab->getFields(); $aName = $aName[0]->getChildren(); $aName=$aName[0];
                    $oAdditionalTab->moveAfter($aMoveField, $aName, $oHomeTab);
                    self::_addClass($aName, 10);
                    self::_addClass($aMoveField, 2);
                }
            }

        /*Перенос полей с основной вкладки*/
            $aMoveFields = array(
                //'image_small_height',
                //'image_small_width',
                //'image_large_height',
                //'image_large_width',
                'parent_id', //Группа группы
                'informationsystem_group_id', //Группа ИС
                'shop_group_id', //Группа ИМ
                'shortcut_group_id[]', //Доп.группа
                //'type',
                'datetime',
                'start_datetime',
                'end_datetime',
                //'showed', //Счетчик показов
                //'marking', //Артикул
                'weight',
                'shop_measure_id',
                //'tags[]',
                //'siteuser_group_id', //Группа доступа
                //'shop_seller_id', //Продавец
                'indexing',
                'active',
                //'length',
                //'width',
                //'height',  
                'modification_id', //Модификация для товара
                'shop_producer_id', //Производитель
                'apply_purchase_discount', //Учитывать для скидки от суммы заказа
                );


            /*Отдельно для каждой формы*/
            switch ($ObjectClass)
            {
                //Элемент
                case 'Informationsystem_Item_Model': case 'Shop_Item_Model':
                    switch ($oInfoShop->id)
                    {
                        case 18:    //Слайдер на главной
                            $aMoveFields = array(
                                'indexing',
                                'active',
                                'start_datetime',
                                'end_datetime',
                                );
                        break;
                    }
                break;

                //Группа
                case 'Informationsystem_Group_Model': case 'Shop_Group_Model':
                break;
            }


            /*Если url формируется через идентификатор*/
                if ($oInfoShop->url_type == 0)
                {
                    //Скрытие вкладок
                    $oSeoTab->active(0);   //SEO
                    if (isset($oSeoTemplatesTab)){ $oSeoTemplatesTab->active(0); }  //Шаблоны SEO

                    //Скрытие полей
                    $oHomeTab->move($controller->getField('path'),$oMainTab); //url эл-та
                    $aMoveFields = array_flip($aMoveFields);
                    unset($aMoveFields['indexing']);  //Индексировать
                    $aMoveFields = array_flip($aMoveFields);                    
                }


            foreach (array_reverse($oMainTab->getFields()) as $aField)
            {
                foreach (array_reverse($aField->getChildren()) as $aChild)
                {
                    if (isset($aChild->name) && in_array($aChild->name, $aMoveFields))
                    {
                        switch ($aChild->name)
                        {
                            case 'informationsystem_group_id': 
                            case 'shop_group_id':
                            case 'parent_id':
                                self::_addClass($aChild, 2);
                                $aChild->filter(0);
                                break;

                            case 'end_datetime':
                                $aChild->caption('Завершение публикации');
                            case 'shortcut_group_id[]':
                            case 'datetime':
                            case 'start_datetime':                            
                            case 'weight':
                            case 'shop_measure_id':
                                self::_addClass($aChild, 2);
                                break;

                            case 'indexing':
                            case 'active':
                            case 'apply_purchase_discount':
                                $aChild->divAttr(array('style'=>'padding-top:25px', 'class'=>'form-group col-xs-2 col-sm-2'));
                                break;                                
                        }
                        $oMainTab->moveAfter($aChild, $aHomeKeyField, $oHomeTab);
                    }
                }
            }


        /*Отдельно для каждой формы*/
        switch ($ObjectClass)
        {   
            //Инфоэлемент
            case 'Informationsystem_Item_Model': 
            break;

            //ИнфоГруппа
            case 'Informationsystem_Group_Model': 
            break;

            //Товар ИМ
            case 'Shop_Item_Model':

                /*Перенос блоков товара*/
                    $aFields = $oMainTab->getFields();
                    
                    $aArr = array(
                    //    'Комплект',
                        'Цены',
                        'Специальные цены',
                        'Количество товара на складах'
                    );
                    
                    foreach ($aFields as $key => $oField)
                    {
                        $aChilds=$oField->getChildren();
                        
                        if (isset($aChilds[0]) && isset($aChilds[0]->value))
                        {
                            if (in_array($aChilds[0]->value, $aArr, TRUE))
                            {
                                self::_addClass($oField, 4);
                                $oMainTab->move($oField,$oHomeTab);                                
                            }

                        }
                    }

                /*Перенос специальных цен*/              

            break;

            //Группа ИМ
            case 'Shop_Group_Model':
            break;
        }

        /*Перенос доп.св-тв на другие вкладки*/
            foreach ($oPropertyTab->getFields() as $aField)
            {
                $aChilds=$aField->getChildren();
                foreach ($aChilds as $aChild)
                {
                    $aChi=$aChild->getChildren();

                    if (get_class($aChi[0]) == 'Skin_Bootstrap_Admin_Form_Entity_Div')   //Раздел доп.свойств
                    {
                        foreach ($aChi as $aGroup)
                        {
                            if (get_class($aGroup) == 'Skin_Bootstrap_Admin_Form_Entity_Div')
                            {
                                $groupChi=$aGroup->getChildren();

                                if (get_class($groupChi[0]) == 'Skin_Bootstrap_Admin_Form_Entity_Select') //Скрываем выбор группы связанных ИС/ИМ, если их нет (!!! ПРОВЕРИТЬ)
                                {
                                    if ( (count($groupChi[0]->options) < 2 && count($groupChi[1]->options) > 1) )
                                    {
                                        self::_delGroup($groupChi[0]);
                                    }
                                }

                                self::_settingsProps($groupChi[0]);
                            }
                        }
                    }
                    else    //Отдельное свойство
                    {
                        self::_settingsProps($aChi[0]);
                    }
                }

                $oMovePropertyTab = false;  //Вкладка перемещения для всех
                $oNewPropertyTab = false;   //Вкладка перемещения для всех, кроме default
                $propCaption = strtolower($aField->caption);

                switch ($propCaption)
                {
                    case 'description': $oNewPropertyTab = $oDescriptionTab;    break;
                    case 'additional':  $oNewPropertyTab = $oAdditionalTab;     break;
                    case 'exportimport':$oNewPropertyTab = $oExportImportTab;   break;
                    case 'associateds': $oNewPropertyTab = $oAssociatedsTab;    break;
                    case 'seo':         $oNewPropertyTab = $oSeoTab;            break;
                    case 'home': if ($home) { $oNewPropertyTab = $oHomeTab; }   break;
                    case 'group':if (!$home){ $oNewPropertyTab = $oHomeTab; }   break;

                    default:            $oMovePropertyTab = $oHomeTab;          break;
                }

                if ($oNewPropertyTab)
                {
                    $oMovePropertyTab = $oNewPropertyTab;
                    $aField->caption('Дополнительные свойства');
                }

                $oMovePropertyTab && $oPropertyTab->move($aField, $oMovePropertyTab);
            }

        /*Скрытие вкладок*/
            $oMainTab->active(0);
            $oAdditionalTab->active(0); //Дополнительные
            $oPropertyTab->active(0); //Доп. св-ва
            if (isset($oExportImportTab)){ $oExportImportTab->active(0); }
            if (isset($oAssociatedsTab)){ $oAssociatedsTab->active(0); }  //Сопутствующие

            /*Отдельно для каждой формы*/
            switch ($ObjectClass)
            {
                //Элемент
                case 'Informationsystem_Item_Model': case 'Shop_Item_Model':
                    switch ($oInfoShop->id)
                    {
                        case 1:
                            $oDescriptionTab->active(0); //Описание
                        break;
                    }
                break;

                //Группа
                case 'Informationsystem_Group_Model': case 'Shop_Group_Model':
                break;
            }

        /*Стили*/
            $controller->addContent('
                <style>
                    .img-hide .row>.form-group:last-of-type,
                    .img-hide .row>.form-group:nth-of-type(3),
                    .group-hide>.row>div:first-of-type~*, .group-hide select
                    {
                        display:none !important
                    }
                </style>');

        return true;
    }
    

    /*Правила для формы Структуры*/
    static protected function _Form_Structure($controller, $ObjectClass) //Контроллер и Тип формы
    {
        /*Замена частоты обновления Sitemap на Индексировать дочерние элементы*/
            $oldField=$controller->getField('changefreq'); //Св-во, тип которого хотим переделать

            //Удаление частоты обновления
                $fields=$controller->getTabs();
                $fields=$fields[1]->getChildren();
                $fields[0]->deleteChild(0);

            //Создание нового поля  
                //$newField = Admin_Form_Entity::factory('Checkbox')
                $newField = Admin_Form_Entity::factory('Select')
                    ->options(array(
                        'Не индексировать',
                        'Индексировать',
                        'Индексировать только группы'
                        ))
                    ->name($oldField->name)             //Имя
                    ->divAttr(array('class'=>'form-group col-xs-4'))       //Классы
                    ->value($oldField->value<3 ? $oldField->value : 1) //Значение
                    ->caption('<acronym title="Индексировать дочерние элементы">Дочерние элементы</acronym>');

                $fields[0]->add($newField);

        return true;
    }


    /*Смена макета по умолчанию*/
    static protected function _Form_Layout($controller, $ObjectClass) //Контроллер и Тип формы
    {
        $layout=15; //id макета по умолчанию

        $object = $controller->getObject();
        if ($object->template_id==0)
        {
            $object->template_id($layout);
            if (isset($object->Document)) $object->Document->template_id($layout);
        }
        return true;
    }


    /*Отображение настроек для Скрытия малых изображений в ИМ и ИС*/
    static protected function _Form_Img_Hide_Settings($controller, $ObjectClass) //Контроллер и Тип формы
    {
        $field=$controller->getField('typograph_default_items');
        $field->caption('<acronym title="">Скрывать поле малого изображения элементов</acronym>');
        
        $field=$controller->getField('typograph_default_groups');
        $field->caption('<acronym title="">Скрывать поле малого изображения групп</acronym>');

        $field=$controller->getField('apply_tags_automatically');
        $field->caption('<acronym title="">Разрешить добавление групп</acronym>');

        $field=$controller->getField('apply_keywords_automatically');
        $field->caption('<acronym title="">Разрешить добавление элементов в корне</acronym>');

        if ($ObjectClass == 'Shop_Model')
        {
            $field=$controller->getField('reserve');
            $field->caption('<acronym>Учитывать количество товара на складе</acronym>');
        }
        
        return true;
    }


    static public function onBeforeExecute($controller, $args)
    {
        list($operation, $Admin_Form_Controller) = $args;
        if (is_null($operation))
        {
            $ObjectClass = get_class($controller->getObject()); //Класс объекта

            switch ($ObjectClass)
            {   
                //Товар и Группа ИМ
                case 'Shop_Item_Model':
                case 'Shop_Group_Model': 
                    self::_Form_All($controller, $controller->getObject(), $ObjectClass);
                break;

                //Структура и Документ
                case 'Structure_Model': 
                case 'Document_Model': 
                    self::_Form_Layout($controller, $ObjectClass);
                break;
            }
        }
    }


    static public function onAfterRedeclaredPrepareForm($controller, $args)
    {
        list($object, $Admin_Form_Controller) = $args;
        $ObjectClass = get_class($controller->getObject()); //Класс объекта

        switch ($ObjectClass)
        {   
            //ИС и ИМ
            case 'Shop_Model':
            case 'Informationsystem_Model':
                if (defined('HOOK_FORM_IMG_HIDE') and HOOK_FORM_IMG_HIDE)  //Вкл константа Скрытия малых изображений
                {
                    self::_Form_Img_Hide_Settings($controller, $ObjectClass);
                }
            break;


            //Инфоэлемент и ИнфоГруппа
            case 'Informationsystem_Item_Model':
            case 'Informationsystem_Group_Model':
                self::_Form_All($controller, $controller->getObject(), $ObjectClass);
            break;

            //Структура
            case 'Structure_Model':
                self::_Form_Structure($controller, $ObjectClass);
            break;
        }

    }


    /*Переименовываем и скрываем кнопки Добавления*/
    static public function onBeforeShowContent($controller)
    {
        if (isset($controller->request['informationsystem_id']) || isset($controller->request['shop_id']))
        {
            $aChilds = $controller->getChildren();
            $aInfoShop = array('type' => isset($controller->request['shop_id']) ? 'shop' : 'informationsystem');    //Тип
            $aInfoShop['id'] = $controller->request[$aInfoShop['type'].'_id'];      //id ИС/ИМ
            $home = !isset($controller->request[$aInfoShop['type'].'_group_id']);   //Находимся в корне

            if ($aInfoShop['type']=='shop')
            {
                $oInfoShop = Core_Entity::factory('Shop', $aInfoShop['id']);
            }
            else
            {
                $oInfoShop = Core_Entity::factory('Informationsystem', $aInfoShop['id']);
            }

            $hide_items = $oInfoShop->apply_keywords_automatically==0 && $home;        //Запретить добавление элементов в корне
            $hide_groups = $oInfoShop->apply_tags_automatically==0 && !$hide_items;    //Запретить добавление групп

            foreach ($aChilds as $aChild) if (get_class($aChild) == 'Skin_Bootstrap_Admin_Form_Entity_Menus') { break; }
            $aButtons = $aChild->getChildren();

            $delId = $comId = -1;
            foreach ($aButtons as $i => $aButton)
            {
                switch ($aButton->name)
                {
                    case 'Информационный элемент': case 'Товар':
                        if ($hide_items)    //Скрываем кнопку
                        {
                            $delId = $i;
                        }
                        else
                        {
                            switch ($aInfoShop['id'])  //Переименовываем кнопку
                            {
                                case 1: $aButton->name = 'ff'; break;
                            }
                        }
                        break;
                    
                    case 'Информационная группа': case 'Группа':
                        if ($hide_groups)    //Скрываем кнопку
                        {
                            $delId = $i;
                        }
                        else
                        {
                            switch ($aInfoShop['id'])  //Переименовываем кнопку
                            {
                                case 1: $aButton->name = 'ff'; break;
                            }
                        }
                        break;

                    case 'Комментарии':
                        $comId = $i;
                        break;
                }
            }
            if ($comId>-1)
            {
                $aChild->deleteChild($comId);
            }
            if ($delId>-1)
            {
                $aChild->deleteChild($delId);
            }

        }
    }
}