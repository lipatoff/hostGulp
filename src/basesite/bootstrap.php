<?php
/**
 * HostCMS bootstrap file.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2015 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
define('CMS_FOLDER', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('HOSTCMS', TRUE);

// ini_set("memory_limit", "128M");
// ini_set("max_execution_time", "120");

// Константа запрещает выполнение ini_set, по умолчанию false - разрешено
define('DENY_INI_SET', FALSE);

// Запрещаем установку локали, указанной в параметрах сайта
// define('ALLOW_SET_LOCALE', FALSE);
setlocale(LC_NUMERIC, "POSIX");

if (!defined('DENY_INI_SET') || !DENY_INI_SET)
{
	ini_set('display_errors', 1);

	if (version_compare(PHP_VERSION, '5.3', '<'))
	{
		/* Решение проблемы trict Standards: Implicit cloning object of class 'kernel' because of 'zend.ze1_compatibility_mode' */
		ini_set('zend.ze1_compatibility_mode', 0);

		set_magic_quotes_runtime(0);
		ini_set('magic_quotes_gpc', 0);
		ini_set('magic_quotes_sybase', 0);
		ini_set('magic_quotes_runtime', 0);
	}
}

require_once(CMS_FOLDER . 'modules/core/core.php');

Core::init();

date_default_timezone_set(Core::$mainConfig['timezone']);


/*Только для авторизованных*/
if (Core_Auth::logged())
{
	// Observers
	Core_Event::attach('Xsl_Processor.onBeforeProcess', array('Xsl_Processor_Observer', 'onBeforeProcess'));
	Core_Event::attach('Xsl_Processor.onAfterProcess', array('Xsl_Processor_Observer', 'onAfterProcess'));
	Core_Event::attach('Core_Cache.onBeforeGet', array('Core_Cache_Observer', 'onBeforeGet'));
	Core_Event::attach('Core_Cache.onAfterGet', array('Core_Cache_Observer', 'onAfterGet'));
	Core_Event::attach('Core_Cache.onBeforeSet', array('Core_Cache_Observer', 'onBeforeSet'));
	Core_Event::attach('Core_Cache.onAfterSet', array('Core_Cache_Observer', 'onAfterSet'));

	if (defined('HOOK_FORM') and HOOK_FORM) 		//Редактирование форм администрирования
	{
		Core_Event::attach('Admin_Form_Action_Controller_Type_Edit.onAfterRedeclaredPrepareForm', array('Lmodules_Form', 'onAfterRedeclaredPrepareForm'));
		Core_Event::attach('Admin_Form_Action_Controller_Type_Edit.onBeforeExecute', array('Lmodules_Form', 'onBeforeExecute'));
		Core_Event::attach('Admin_Form_Controller.onBeforeShowContent', array('Lmodules_Form', 'onBeforeShowContent'));
	}
	if (defined('HOOK_REDIRECT') and HOOK_REDIRECT) //Авторедиректы
	{
		//Сохранение в форме
		Core_Event::attach('Admin_Form_Action_Controller_Type_Edit.onBeforeExecute', array('Lmodules_Redirect', 'onBeforeExecute'));
		Core_Event::attach('Admin_Form_Action_Controller_Type_Edit.onAfterExecute', array('Lmodules_Redirect', 'onAfterExecute'));

		//Удаление
		Core_Event::attach('informationsystem_item.onBeforeMarkDeleted', 	array('Lmodules_Redirect', 'onBeforeMarkDeleted'));
		Core_Event::attach('informationsystem_group.onBeforeMarkDeleted', 	array('Lmodules_Redirect', 'onBeforeMarkDeleted'));
		Core_Event::attach('shop_item.onBeforeMarkDeleted', 				array('Lmodules_Redirect', 'onBeforeMarkDeleted'));
		Core_Event::attach('shop_group.onBeforeMarkDeleted', 				array('Lmodules_Redirect', 'onBeforeMarkDeleted'));
		Core_Event::attach('structure.onBeforeMarkDeleted', 				array('Lmodules_Redirect', 'onBeforeMarkDeleted'));

		//Восстановление из корзины
		Core_Event::attach('informationsystem_item.onBeforeUndelete', 	array('Lmodules_Redirect', 'onBeforeUndelete'));
		Core_Event::attach('informationsystem_group.onBeforeUndelete', 	array('Lmodules_Redirect', 'onBeforeUndelete'));
		Core_Event::attach('shop_item.onBeforeUndelete', 				array('Lmodules_Redirect', 'onBeforeUndelete'));
		Core_Event::attach('shop_group.onBeforeUndelete', 				array('Lmodules_Redirect', 'onBeforeUndelete'));
		Core_Event::attach('structure.onBeforeUndelete', 				array('Lmodules_Redirect', 'onBeforeUndelete'));

		//Перемещение
		Core_Event::attach('informationsystem_item.onBeforeMove', 	array('Lmodules_Redirect', 'onBeforeMove'));
		Core_Event::attach('informationsystem_group.onBeforeMove', 	array('Lmodules_Redirect', 'onBeforeMove'));
		Core_Event::attach('shop_item.onBeforeMove', 				array('Lmodules_Redirect', 'onBeforeMove'));
		Core_Event::attach('shop_group.onBeforeMove', 				array('Lmodules_Redirect', 'onBeforeMove'));
	}
	//if (defined('HOOK_IMGCACHE') and HOOK_IMGCACHE) //Обновление кэша изображений (НЕ РАБОТАЕТ!!!!!)
	//{
	//	Core_Event::attach('Admin_Form_Action_Controller_Type_Edit.onBeforeExecute', array('Lmodules_Content', 'onBeforeExecuteImgCache'));
	//}
	if (defined('HOOK_DATASIZE') and HOOK_DATASIZE) //Добавление data-size в href на img
	{
		Core_Event::attach('Admin_Form_Action_Controller_Type_Edit.onBeforeExecute', array('Lmodules_Content', 'onBeforeExecuteImgHref'));
	}
	if (defined('HOOK_DATASRC') and HOOK_DATASRC) 	//Замена src на data-src
	{
		Core_Event::attach('Admin_Form_Action_Controller_Type_Edit.onBeforeExecute', array('Lmodules_Content', 'onBeforeExecuteDataSrc'));
	}
	if (defined('HOOK_IMGSIZE') and HOOK_IMGSIZE) 	//Добавление размеров к img data-src
	{
		Core_Event::attach('Admin_Form_Action_Controller_Type_Edit.onBeforeExecute', array('Lmodules_Content', 'onBeforeExecuteImgSize'));
	}

}


if (defined('HOOK_SHOP') and HOOK_SHOP) 	// Хуки для интернет-магазина
{	
	Core_Event::attach('shop_item.onBeforeAddAssociatedEntity', array('Lmodules_Shop', 'onBeforeAddAssociatedEntity')); //Выборка связанных товаров
}


//Проверка ответа сервера. Если 404/301 - включаем проверку редиректов
Core_Event::attach('Core_Response.onBeforeSendHeaders', array('Lmodules_Redirect', 'onBeforeSendHeaders'));


// Windows locale
//setlocale(LC_ALL, array ('ru_RU.utf-8', 'rus_RUS.utf8'));

//Шорткоды везде
Core_Event::attach('Core_Response.onBeforeCompress', array('Lmodules_Content', 'applyShortcodes'));

//Кастомные контроллеры
	require_once(CMS_FOLDER . 'modules/lmodules/controllers.php');

//Кастомные библиотеки
	require_once(CMS_FOLDER . 'modules/lmodules/library.php');	//Общие
	require_once(CMS_FOLDER . 'modules/lmodules/lib_filter.php');	//Фильтр