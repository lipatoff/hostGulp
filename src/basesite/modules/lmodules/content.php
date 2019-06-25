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

class Lmodules_Content extends Admin_Form_Action_Controller_Type_Edit
{
/*Обновление кэша изображений (НЕ РАБОТАЕТ!!!!!)*/
    static public function onBeforeExecuteImgCache($controller, $args)
    {

        function insertImgCache($content)
        {
            return $content;
        }

        //Если происходит событие сохранения
        if ($args[0]!=NULL){ 

            //Определяем класс контроллера
            $class=get_class($controller);

            //Все допустимые классы для правки, где есть large_ и small_image
            $controllers=array(
                'Shop_Item_Controller_Edit',
                'Informationsystem_Item_Controller_Edit'
                );

            //Если класс контроллера подходит 
            if (in_array($class,$controllers)) {
                $object = $controller->getObject();
                $image_small = $object->__get('image_small');
                if (strstr($image_small, '?', true)) $image_small = strstr($image_small, '?', true);

                $controller->_formValues['small_image'] = $image_small.'?'.$controller->_formValues['_'];
                

                $object->__set('image_small', $image_small.'?'.$controller->_formValues['_']);
                print_r($controller);

                //if ( !(isset($controller->_formValues['small_image'])) ) {....}

            }

            //Все допустимые классы для правки, где есть доп. св-ва
            $controllers=array(
                'Shop_Item_Controller_Edit',
                'Informationsystem_Item_Controller_Edit',
                'Structure_Controller_Edit'
                );

            //Если класс контроллера подходит 
            if (in_array($class,$controllers)) {

            }

        }
    }


/*Добавление data-size в href на img*/
    static public function onBeforeExecuteImgHref($controller, $args)
    {

        function insertImgHref($content)
        {
            //Обновлять data-size
            /*$content = preg_replace('/data-size="[0-9x]*" /i', '', $content);
            preg_match_all('/href="([^>]*?\.(jpg|png|gif|bmp))/i', $content, $hrefs);
            $hrefs0=$hrefs[0];
            $hrefs1=$hrefs[1];*/

            //Не обновлять data-size
            preg_match_all('/<a((?!>|data-size).)*(href="([^>]*?\.(jpg|png|gif|bmp)))((?!>|data-size).)*>/i', $content, $hrefs);
            if (count($hrefs[0])>0)
            {
                $hrefs0=$hrefs[2];
                $hrefs1=$hrefs[3];

                foreach ($hrefs0 as $i => $href) {
                    //Определяет внутренняя или внешняя ссылка
                    if (substr($hrefs1[$i], 0, 1)=='/') $hrefs1[$i]=$_SERVER['DOCUMENT_ROOT'].$hrefs1[$i]; 
                    $size = GetImageSize($hrefs1[$i]);
                    $res = 'data-size="'.$size[0].'x'.$size[1].'" '.$href;
                    $content = str_replace($href, $res, $content);
                }
                Core_Message::show("Атрибут 'data-size' добавлен.");
            }
            return $content; 
        }

        //Если происходит событие сохранения
        if ($args[0]!=NULL){ 

            //Определяем класс контроллера
            $class=get_class($controller);

            //Все допустимые классы для правки
            $controllers=array(
                'Shop_Item_Controller_Edit',
                'Informationsystem_Item_Controller_Edit',
                'Document_Controller_Edit',
                'Structure_Controller_Edit'
                );

            //Если класс контроллера подходит 
            if (in_array($class,$controllers)) {
                if (isset($controller->_formValues['text']))            $controller->_formValues['text']=insertImgHref($controller->_formValues['text']);
                if (isset($controller->_formValues['description']))     $controller->_formValues['description']=insertImgHref($controller->_formValues['description']);
                if (isset($controller->_formValues['document_text']))   $controller->_formValues['document_text']=insertImgHref($controller->_formValues['document_text']);
            }
        }
    }


/*Замена src на data-src*/
    static public function onBeforeExecuteDataSrc($controller, $args)
    {

        function insertDataSrc($content)
        {
            preg_match_all('/<img[^>]*( src="([^"]*)")[^>]*>/i', $content, $imgs);
            if (count($imgs[0])>0)
            {           
                $imgs0=$imgs[0]; // <img src="/images/blog/cherehapa1.jpg" alt="Страховка">
                $imgs1=$imgs[1]; //  src="/images/blog/cherehapa1.jpg"
                $imgs2=$imgs[2]; // /images/blog/cherehapa1.jpg

                foreach ($imgs0 as $i => $img) {
                    //Удаляем data-src, если есть
                    $imgnew = preg_replace('/ data-src="([^"]*)"/i', '', $img);

                    $res = ' data-'.substr($imgs1[$i], 1);
                    $imgnew = str_replace($imgs1[$i], $res, $imgnew);
                    $content = str_replace($img, $imgnew, $content);
                }
                Core_Message::show("Атрибут 'src' => 'data-src'");
            }
            return $content; 
        }

        //Если происходит событие сохранения
        if ($args[0]!=NULL){ 

            //Определяем класс контроллера
            $class=get_class($controller);

            //Все допустимые классы для правки
            $controllers=array(
                'Shop_Item_Controller_Edit',
                'Informationsystem_Item_Controller_Edit',
                'Document_Controller_Edit',
                'Structure_Controller_Edit'
                );

            //Если класс контроллера подходит 
            if (in_array($class,$controllers)) {
                if (isset($controller->_formValues['text']))            $controller->_formValues['text']=insertDataSrc($controller->_formValues['text']);
                if (isset($controller->_formValues['description']))     $controller->_formValues['description']=insertDataSrc($controller->_formValues['description']);
                if (isset($controller->_formValues['document_text']))   $controller->_formValues['document_text']=insertDataSrc($controller->_formValues['document_text']);
            }
        }
    }


/*Добавление размеров к img data-src*/
    static public function onBeforeExecuteImgSize($controller, $args)
    {

        function insertImgSize($content)
        {
            preg_match_all('/<img[^>]*(data-src="([^"]*)")[^>]*>/i', $content, $imgs);
            if (count($imgs[0])>0)
            {
                $imgs0=$imgs[0]; // <img data-src="/images/blog/cherehapa1.jpg" alt="Страховка">
                $imgs1=$imgs[1]; // data-src="/images/blog/cherehapa1.jpg"
                $imgs2=$imgs[2]; // /images/blog/cherehapa1.jpg

                foreach ($imgs0 as $i => $img) {
                    if (!preg_match('/(width=.*height=|height=.*width=)/i', $img)){
                        //Определяет внутренняя или внешняя ссылка
                        if (substr($imgs2[$i], 0, 1)=='/') $imgs2[$i]=$_SERVER['DOCUMENT_ROOT'].$imgs2[$i]; 
                        $size = GetImageSize($imgs2[$i]);

                        //Удаляем width и height
                        $imgnew = preg_replace('/(width|height)="[0-9%]*"/i', '', $img);
                        
                        $res = $imgs1[$i].' width="'.$size[0].'" height="'.$size[1].'" ';
                        $imgnew = str_replace($imgs1[$i], $res, $imgnew);
                        $content = str_replace($img, $imgnew, $content);
                    }
                }
                Core_Message::show("Размеры img добавлены.");
            }
            return $content; 
        }

        //Если происходит событие сохранения
        if ($args[0]!=NULL){ 

            //Определяем класс контроллера
            $class=get_class($controller);

            //Все допустимые классы для правки
            $controllers=array(
                'Shop_Item_Controller_Edit',
                'Informationsystem_Item_Controller_Edit',
                'Document_Controller_Edit',
                'Structure_Controller_Edit'
                );

            //Если класс контроллера подходит 
            if (in_array($class,$controllers)) {
                if (isset($controller->_formValues['text']))            $controller->_formValues['text']=insertSizeImgSize($controller->_formValues['text']);
                if (isset($controller->_formValues['description']))     $controller->_formValues['description']=insertSizeImgSize($controller->_formValues['description']);
                if (isset($controller->_formValues['document_text']))   $controller->_formValues['document_text']=insertSizeImgSize($controller->_formValues['document_text']);
            }
        }
    }


/*Добавление фона в img*/
    static public function onBeforeExecuteImgColor($controller, $args)
    {
        //Вычисление цвета
        function getcol($img){
            if (substr($img, 0, 1)=='/') $img=$_SERVER['DOCUMENT_ROOT'].$img;
            $style='';

            /*Вариант 1: цвет*/
            /*
            $color = ColorThief::getPalette($img, 2, 100);
            $style='rgb('.$color[0][0].','.$color[0][1].','.$color[0][2].')';
            */

            /*Вариант 2: градиент*/
            
            $colors = ColorThief::getGradient($img, 100, 4);
            
            $style='linear-gradient(180deg';
            foreach ($colors as $color) { $style.=', rgb('.$color[0].','.$color[1].','.$color[2].')'; }
            $style.=')';
            
            /**/
            $style = ' data-bk="'.$style.'"';
            return $style;
        }

        //Добавляет цветовой фон к img
        function insertColorImg($content)
        {
            //Не обновлять 
            preg_match_all('/<img((?!>|data-bk).)*(src="([^"]*)")((?!>|data-bk).)*>/i', $content, $imgs);
            if (count($imgs[0])>0)
            {
                $imgs0=$imgs[2];
                $imgs1=$imgs[3];

                foreach ($imgs0 as $i => $img) {
                    $res=$img.getcol($imgs1[$i]);

                    $content = str_replace($img, $res, $content);
                }
                Core_Message::show("Фон img добавлен (data-bk).");
            }
            return $content; 
        }


        //Если происходит событие сохранения
        if ($args[0]!=NULL){ 

            //Определяем класс контроллера
            $class=get_class($controller);

            //Все допустимые классы для правки
            $controllers=array(
                'Shop_Item_Controller_Edit',
                'Informationsystem_Item_Controller_Edit',
                'Document_Controller_Edit',
                'Structure_Controller_Edit'
                );

            //Если класс контроллера подходит 
            if (in_array($class,$controllers)) {
                if (isset($controller->_formValues['text']))            $controller->_formValues['text']=insertColorImg($controller->_formValues['text']);
                if (isset($controller->_formValues['description']))     $controller->_formValues['description']=insertColorImg($controller->_formValues['description']);
                if (isset($controller->_formValues['document_text']))   $controller->_formValues['document_text']=insertColorImg($controller->_formValues['document_text']);
            }
        }
    }


/*Шорткоды везде*/
    static public function applyShortcodes(Core_Response $oResponse)
    {
        $sContent = $oResponse->getBody();
        $sContent = Shortcode_Controller::instance()->applyShortcodes($sContent);
        $oResponse->changeBody($sContent);
    }

}