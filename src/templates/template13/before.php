<?php 
function upcache($file){return file_exists('.'.$file) ? $file.'?'.filemtime('.'.$file) : '';}	 //Обновление кэш, если файл изменен
//$a=microtime(true);
$htmlcompress=false; //Сжатие HTML

//Если speedtest
	$pageSpeed = isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Google Page Speed Insights') > 0 || strpos($_SERVER['HTTP_USER_AGENT'], 'PTST') > 0 || strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome-Lighthouse') > 0);


if (Core_Registry::instance()->get('ajax'))	//Если передан ajax - то страницу не обрабатываем
{
	$aAjax = Core_Registry::instance()->get('ajax');
	$content = $aAjax['data'];
}
else
{
	ob_start();

	//Домен сайта
		$Site = isset($_SERVER['HTTP_X_HTTPS']) ? 'https://' : 'http://';
		$Site .= $_SERVER['HTTP_HOST'];

	//url раздела структуры
		$homePage = Core_Page::instance()->structure->path == '/';

	//Индексация и каноникал
		$bNoactive = Core_Registry::instance()->get('bNoactive');
		$bNoindex = Core_Registry::instance()->get('bNoindex') ? true : !Core_Page::instance()->structure->indexing;
		if (!$bNoindex){
			$canonical = $Site.str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
			$canonical = preg_replace('/\/page-[0-9].*/', '/', $canonical);
			$canoindex = '<link rel="canonical" href="'.$canonical.'"/>';
		}else{ $canoindex = '<meta name="robots" content="noindex, follow" />'; }

	//Метатеги
		$og_title = Core_Page::instance()->title;				$low_title = mb_strtolower($og_title);
		$og_description = Core_Page::instance()->description;	$low_description = mb_strtolower($og_description);
		$og_keywords = mb_strtolower(Core_Page::instance()->keywords);
		$og_image = Core_Registry::instance()->get('image') ? Core_Registry::instance()->get('image') : '/images/logo.png';

		//Шаблон мета-тегов
		/*
			if ((strpos($low_title, 'школ')===false) || (strpos($low_title, 'каула-йог')===false)) { $og_title.=' | НАЗВАНИЕ КОМПАНИИ'; }
			if ((strpos($low_description, 'школ')===false) || (strpos($low_description, 'каула-йог')===false)) { $og_description = rtrim($og_description, ' \t.').' - занятия в Москве | НАЗВАНИЕ КОМПАНИИ.'; }
			if ((strpos($og_keywords, 'школ')===false) || (strpos($og_keywords, 'каула-йог')===false)) { $og_keywords .= ', название компании'; }
		*/

		$og_title = str_replace('<br/>', '', str_replace('&nbsp;', ' ', $og_title));
		$og_description = str_replace('<br/>', '', str_replace('&nbsp;', ' ', $og_description));
		$og_keywords = str_replace('<br/>', '', str_replace('&nbsp;', ' ', $og_keywords));		

	//Проверка ширины устройства
	/*
		Core_Utils::mobileDetect();
	*/

	//Вызываемые области
		$oPhone1=Core_Entity::factory('Document', '14')->Document_Versions->getCurrent();
		
?>