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

class Lmodules_Redirect extends Admin_Form_Action_Controller_Type_Edit
{
/*Проверка ответа сервера. Если 404/301 - включаем проверку редиректов*/
    static public function onBeforeSendHeaders($object)
    {
        if ($object->getStatus() != 200)
        {
            $href=urldecode($_SERVER['REQUEST_URI']);
            $newhref=''; $reservhref='';

            /*Сохранение меток*/
                $utm='';
                if (strpos($href, '?')>0){
                    $utm_pos=strpos($href, '?');
                    $utm=substr($href, $utm_pos);
                    $href=substr($href, 0, $utm_pos);
                }

            /*Если адрес со старого сайта*/
                $old_site = (strpos($href, '.html')>0) || (strpos($href, '.php')>0);

            /*Преобразование url из старого сайта*/
                /*
                if (strpos($href, 'seminars/') == 1)
                {
                    $href = str_replace('seminars/', 'meropriyatiya/', $href);
                    $reservhref = '/meropriyatiya/';
                }
                */

            /*Автоматические редиректы страниц hostcms*/
                $href_no_slash = false;
                if (!$old_site && (substr($href, -1) != '/')) 
                {
                    $href.='/';
                    $href_no_slash = true;
                }                

            /*Если есть редирект - редиректим*/
                $file_path = $_SERVER['DOCUMENT_ROOT'].'/redirect/hostcms.php';
                if (file_exists($file_path))
                {
                    $r = array();
                    $r_del = array();
                    require_once($file_path);

                    if (isset($r[$href]))   //Если есть прямой редирект
                    {
                        $newhref = $r[$href];
                    }
                    elseif ( !$old_site && (substr_count($href,'/') > 2) && !isset($r_del[$href]) && !in_array($href, $r) && !in_array($href, $r_del) ) //Иначе, если редирект не удален, поиск редиректов в разделах 
                    {
                        do
                        {
                            $href_before = $href;       //Сохраняем url
                            $newhref_old = $newhref;    //Сохраняем новый url, или пустоту
                            do
                            {
                                $href_before = substr($href_before, 0, strrpos(substr($href_before,0,-1), '/')).'/'; //Отсекаем последний раздел в url

                                if (isset($r[$href_before]))    //Если есть редирект - обновляем url
                                {
                                    $href_after = substr($href, strlen($href_before));
                                    $newhref = $href = $r[$href_before].$href_after;
                                    break;
                                }
                                elseif(isset($r_del[$href_before]) || in_array($href_before, $r_del)) //Если редирект удален
                                {
                                    break;
                                }
                            }
                            while (substr_count($href_before,'/')>2);   //Цикл длится до корня
                        }
                        while ($newhref_old != $newhref);   //Если url обновился - то повторяем цикл с новым url, на поиск других редиректов
                    }
                }

            /*Редиректы разделов со старого сайта*/
                if ($old_site && ($newhref=='')) 
                {
                    $file_path = $_SERVER['DOCUMENT_ROOT'].'/redirect/part_old.php';
                    if (file_exists($file_path))
                    {
                        $r = array();
                        require_once($file_path);
                        foreach ($r as $i => $r_new)
                        {
                            if (strpos($href, $i) == 1)
                            {
                                $newhref = $r_new;
                            }
                        }
                    }
                }

            /*Редиректим*/
                function goredir($newhref,$utm)
                {               
                    if ($newhref!='')
                    {
                        if ($utm!='') {$newhref=$newhref.$utm;} //Ставим utm, если есть
                        $protocol = isset($_SERVER['HTTP_X_HTTPS']) ? 'https://' : 'http://';
                        $location = $protocol.$_SERVER['SERVER_NAME'].$newhref;
                        $headers = get_headers($location);

                        if (strpos(' '.$headers[0], '200')) //Если страница доступна - редиректим
                        {
                            header('HTTP/1.1 301 Moved Permanently'); 
                            header('Location: '.$location); 
                            exit();
                        }

                        return array($headers[0], $location);
                    }
                    return NULL;
                }

                goredir($newhref,$utm);

                if ($reservhref != '')
                {
                    $newhref = getredir($reservhref, $reservhref);
                    goredir($newhref,$utm);
                }

                if ($href_no_slash)
                {
                    $status = goredir($href,$utm);
                    if (strpos(' '.$status[0], '404'))
                    {
                        $handle = curl_init();
                            curl_setopt($handle, CURLOPT_URL, $status[1]);
                            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                            $homepage = curl_exec($handle);
                            curl_close($handle);
                        header("HTTP/1.0 404 Not Found");
                        echo $homepage;
                        exit();
                    }
                }
        }
    }


/*Работа с редиректами self::_addRedirect($object, $oldPath);*/
    static protected function _addRedirect($object, $oldPath) //Объект, предыдущий url
    {
        $class = get_class($object);

        //Если новый url != старому
        if ($object->getPath() != $oldPath && $object->getPath() != '/')
        {
            //Получаем url'ы структуры
                switch ($class)
                {
                    case 'Shop_Item_Model':   //ИМ
                    case 'Shop_Group_Model':
                        $structPath = $object->Shop->structure_id > 0 ? $object->Shop->Structure->getPath() : false;
                        break;
                    case 'Informationsystem_Item_Model':  //ИС
                    case 'Informationsystem_Group_Model':
                        $structPath = $object->Informationsystem->structure_id > 0 ? $object->Informationsystem->Structure->getPath() : false;
                        break;
                    case 'Structure_Model':  //Структура
                        $structPath = '';
                        break;
                }

            //Если узел структуры существует
            if (!($structPath===false))
            {
                $newPath = $structPath.$object->getPath();      //Новый полный путь
                $oldPath = $oldPath !='/' ? $structPath.$oldPath : false;    //Старый полный путь (если есть)
                $undel = false;     //найден редирект для восстановления

                $protocol = isset($_SERVER['HTTP_X_HTTPS']) ? 'https://' : 'http://';
                $location = $protocol.$_SERVER['SERVER_NAME'].$newPath;
                $headers = get_headers($location);
                $activeNewPath = (strpos(' '.$headers[0], '200')); //Если страница доступна - добавляем редирект
                $redirectPath = $activeNewPath || $oldPath; //Если новый url доступен, или элемент меняет url

                $r = $r_del = array();  //хранит весь массив текущих, удаленных и неактивных редиректов
                
                $file_path = $_SERVER['DOCUMENT_ROOT'].'/redirect/hostcms.php';
                if (file_exists($file_path))    //если файл уже существует - то читаем его
                { 
                    require($file_path);

                    //Удаление дублей по ключам
                        array_unique($r);       
                        array_unique($r_del);

                    //Удаление старых редиректов
                        if ($activeNewPath)
                        {
                            unset($r[$newPath]);
                            unset($r_del[$newPath]);
                        }

                    if ($oldPath)   //если есть старый url
                    {
                        foreach ($r as $key => $value)  //удаление двойных редиректов
                        {
                            if (strpos(' '.$value, $oldPath) == 1)  //если url начинается на старый адрес
                            {
                                $url_end = substr($value, strlen($oldPath));    //конец url, который нужно сохранить (если есть)
                                $r[$key] = $newPath.$url_end;   //Обновляем редирект
                                
                                //Добавляем связанные редиректы
                                    if (($url_end != '') && (strpos(' '.$key, $oldPath) == 1))
                                    {
                                        $key_end = substr($key, strlen($oldPath));
                                        $r[$newPath.$key_end] = $r[$key];
                                    }
                            }
                        }
                    }

                    if ($redirectPath)
                    {
                        foreach ($r_del as $key => $value)  //Возвращаем удаленные редиректы
                        {
                            //if (strpos(' '.$value, $newPath) == 1)
                            if ($value == $newPath)  //если url начинается на нужный адрес
                            {
                                $r[$key] = $value;
                                unset($r_del[$key]);
                                $undel = true;
                            }
                        }
                    }
                }

                //Удаляем ключи, которые совпадают со значениями
                    $del = array();
                    foreach ($r as $key => $value)
                    {
                        if ($key == $value) { $del[] = $key; }
                    }
                    foreach ($del as $d)
                    {
                        unset($r[$d]);
                    }
                
                if ($oldPath)
                {
                    $r[$oldPath] = $newPath;
                }

                asort($r);  //Сортировка

                //Сохранение
                    $r_str = '<?php';
                    foreach ($r as $key => $value)
                    {
                        $r_str .= "\n\$r['".$key."'] = '".$value."';";
                    }
                    foreach ($r_del as $key => $value)
                    {
                        $r_str .= "\n\$r_del['".$key."'] = '".$value."';";
                    }                        
                    Core_File::write($file_path,$r_str);

                //Выводим сообщение
                    if ($oldPath)
                    {
                        Core_Message::show("Редирект '".$oldPath."' => '".$newPath."'.");
                    }
                    if ($undel)
                    {
                        Core_Message::show("Старые редиректы на '".$newPath."' восстановлены.");
                    }
                    if (!$redirectPath)
                    {
                        Core_Message::show("Редирект не предусмотрен");
                    }

            }
        }
    }

/*Перед сохранением формы*/
    static public function onBeforeExecute($controller, $args)
    {
        //Если происходит событие сохранения
        if ($args[0]!=NULL){ 
            $class = get_class($controller);

            //Все допустимые классы для правки
            $controllers=array(
                'Shop_Item_Controller_Edit',
                'Informationsystem_Item_Controller_Edit',
                'Structure_Controller_Edit'
                );
            
            //Если класс контроллера подходит 
            if (in_array($class,$controllers)) {
                $object = $controller->getObject();
                $controller->addAllowedProperty('oldpath');
                $controller->oldpath = $object->path ? $object->getPath() : '/';
            }

            $controller->_formValues['create_small_image_from_large_small_image'] = 1;

            //print_r($controller);
        }
    }

/*После сохранения формы*/
    static public function onAfterExecute($controller, $args)
    {
        //Если передан старый url
        if (isset($controller->oldpath)){ 
            $object = $controller->getObject();
            self::_addRedirect($object, $controller->oldpath);
        }
    }

/*Перемещение элемента*/
    static public function onBeforeMove($object, $args)
    {
        $parent_id = $args[0];
        $oldPath = $object->getPath();
        $class = get_class($object);

        //Все допустимые классы для правки
        $objects=array(
            'Shop_Item_Model',
            'Shop_Group_Model',
            'Informationsystem_Item_Model',
            'Informationsystem_Group_Model'
            );

        if (in_array($class,$objects)) {
            switch ($class)
            {
                case 'Shop_Item_Model':   //Товар ИМ
                    $object->shop_group_id = $parent_id;
                    break;
                case 'Informationsystem_Item_Model':  //Элемент ИС
                    $object->informationsystem_group_id = $parent_id;
                    break;
                default:
                    $object->parent_id = $parent_id;
                    break;
            }

            //Проверка url на уникальность
                $oPath = $object->path;
                $object->checkDuplicatePath();
                if ($oPath != $object->path)
                {
                    Core_Message::show("URL изменен на '".$object->path."'.", 'error');
                }

            self::_addRedirect($object, $oldPath);
        }
    }

/*Работа с удалением/восстановлением self::_addDeleted($object);*/
    static protected function _addDeleted($object, $undelete = FALSE) //Объект, восстановление из корзины
    {
        $class = get_class($object);

        //Все допустимые классы для правки
        $objects=array(
            'Shop_Item_Model',
            'Shop_Group_Model',
            'Informationsystem_Item_Model',
            'Informationsystem_Group_Model',
            'Structure_Model'
            );

        if (in_array($class,$objects)) {

            //Получаем url'ы структуры
                switch ($class)
                {
                    case 'Shop_Item_Model':   //ИМ
                    case 'Shop_Group_Model':
                        $structPath = $object->Shop->structure_id > 0 ? $object->Shop->Structure->getPath() : false;
                        break;
                    case 'Informationsystem_Item_Model':  //ИС
                    case 'Informationsystem_Group_Model':
                        $structPath = $object->Informationsystem->structure_id > 0 ? $object->Informationsystem->Structure->getPath() : false;
                        break;
                    case 'Structure_Model':  //Структура
                        $structPath = '';
                        break;
                }

            //Если узел структуры существует
            if (!($structPath===false))
            {
                $oPath = $structPath.$object->getPath();    //Полный путь

                $r = $r_del = array(); //хранит весь массив текущих и удаленных редиректов
                $search_true = false;     //найден редирект для обновления
                
                $file_path = $_SERVER['DOCUMENT_ROOT'].'/redirect/hostcms.php';
                if (file_exists($file_path))    //если файл уже существует - то читаем его
                { 
                    require($file_path);

                    $old_r = $undelete ? $r_del : $r;
                    $new_r = $undelete ? $r : $r_del;

                    foreach ($old_r as $key => $value)  //Помечаем редиректы как удаленные
                    {
                        if (strpos(' '.$value, $oPath) == 1)  //если url начинается на нужный адрес
                        {
                            $new_r[$key] = $value;
                            unset($old_r[$key]);
                            $search_true = true;
                        }
                    }

                    if ($search_true)
                    {
                        asort($new_r);  //Сортировка

                        $r = $undelete ? $new_r : $old_r;
                        $r_del = $undelete ? $old_r : $new_r;

                        //Сохранение
                            $r_str = '<?php';
                            foreach ($r as $key => $value)
                            {
                                $r_str .= "\n\$r['".$key."'] = '".$value."';";
                            }
                            foreach ($r_del as $key => $value)
                            {
                                $r_str .= "\n\$r_del['".$key."'] = '".$value."';";
                            }                    
                            Core_File::write($file_path,$r_str);

                        //Выводим сообщение
                            if ($undelete)
                            {
                                Core_Message::show("Редирект '".$oPath." восстановлен.");
                            }
                            else
                            {
                                Core_Message::show("Редирект '".$oPath." удален.");
                            }
                    }
                }
            }
        }
    }

/*Удаление элемента*/
    static public function onBeforeMarkDeleted($object)
    {
        self::_addDeleted($object);
    }

/*Восстановление элемента из корзины*/
    static public function onBeforeUndelete($object)
    {
        //Проверка url на уникальность
            $oPath = $object->path;
            $object->checkDuplicatePath();
            if ($oPath != $object->path)
            {
                Core_Message::show("URL изменен на '".$object->path."'.", 'error');
            }

        self::_addDeleted($object, TRUE);
    }

}