<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<title>Parser</title>
	<?php function upcache($file){return $file.'?'.filemtime($file);}	 //Обновление кэш, если файл изменен ?>
	<link rel="stylesheet" href="<?=upcache('style.css'); ?>"/>
</head>
<body>
	
<?php
require_once(dirname(__FILE__) . '/../' . 'bootstrap.php');

$oSite = Core_Entity::factory('Site', 2);
Core::initConstants($oSite);

/*Настройки*/
	$s = [
		'site' => 'http://classtrip.ru',	//url сайта
		'start' => '/kursy/',		//url с которого начинать парсинг
		'type' => 1,			//тип структуры (0 - нет структуры, 1 - по url, 2 - по хлебным крошкам, 3 - по ссылкам на страницах)
		'shortcut' => TRUE,		//создавать ярлыки в смежных группах
		
		'rest' => [				//ограничение по url'ам
			'/kursy/'
		],

		'pq' => [
			'group' => '.catalog',		//Селектор группы
			'item' => '.programs', 	//Селектор элемента
			'groups_search' => '.groups, .other-groups',	//Селектор, со ссылками на группы
			'items_search' => '.card',	//Селектор, со ссылками на элементы
			'title' => 'h1',	//Селектор заголовка
			'breadcrumbs' => '.breadcrumbs'	//Селектор хлебных крошек (если type = 2)
		],

		'get' => FALSE, 	//учитывать url с get-параметрами

		'path' => $_SERVER['DOCUMENT_ROOT'].'/parser',
	];

//Получаем список страниц
require('modules/getUrls.php');
list($s, $pages, $urls) = getUrls($s);

if (!isset($_POST['go_parsing']))
{
	//Формируем структуру
	require('modules/getStructure.php');
	$pages = getStructure($s, $pages, $urls);
	showStructure($s, $pages);
}

//Парсинг страниц
require('modules/goParser.php');




		/*
						$h3 = $item->find('h3');
						$arr['name'] = $h3->find('a')->text();
						$arr['country'] = $h3->find('.text-nowrap')->text();
						$arr['link'] = $h3->find('a')->attr('href');

						$arr['img'] = $item->find('img')->attr('src');

						$star = $item->find('.ct-rating__stars--yellow')->attr('style');
		*/


?>
</body>
</html>