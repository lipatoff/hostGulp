<?php
//Подробнее: https://github.com/falbarRu/Parser
require('Parser.php');
//Подробнее: https://code.google.com/archive/p/phpquery/
require('phpQuery/phpQuery.php');


/*Подходит ли url под ограничения
*
*	$s - массив настроек
*	$url - адрес страницы
*	$urls - массив со списком url'ов
*/
function currentUrl($s, $url, $urls)
{
	if (in_array($url,$urls)) return FALSE;

	if (!count($s['rest'])) return TRUE;

	foreach ($s['rest'] as $rest)
	{
		if (strpos(' '.$url, $rest)==1)
		{
			return TRUE;
		}
	}
	return FALSE;
}


/*	Ищем ссылки
*
*	$s - массив настроек
*	$items - объект phpQuery
*/
function searchLinks($s, $urls, $items)
{
	$nodes = $hrefs = [];

	foreach($items as $item)
	{
		$pqitem = pq($item);
		$parent_html = $pqitem->html();
		if ($pqitem->is('a')) $links = array($item);	//Если селектор - ссылка
		else $links = $pqitem->find('a');	//Если нет - то ищем ссылки внутри

		foreach($links as $link)
		{
			$link = pq($link);
			$href = trim($link->attr('href'));
			$go = TRUE;

			if (!$s['get'] && strpos($href, '?')>0)
			{
		        $utm_pos=strpos($href, '?');
		        $utm=substr($href, $utm_pos);
		        $href=substr($href, 0, $utm_pos);
		    }

			//Если url'а еще нет в массиве
			if ($go && in_array($href, $hrefs)) $go = FALSE;
			//Если не хэш
			if ($go && substr($href, 0, 1)=='#') $go = FALSE;
			
			//url не внешний
			if ($go && substr($href, 0, 4)=='http')
			{
				$go = strpos(' '.$href, $s['site'])==1;
				$href = str_replace($s['site'], '', $href);
			}

			if ($go && substr($href, 0, 1)!='/') $href = $url.$href;

			//Проверка на корректность внутреннего url
			if ($go && count($s['rest'])) $go = currentUrl($s, $href, $urls);

			//Если url подходит - то сохраняем его
			if ($go)
			{
				$nodes[] = [
					'href' => $href, 
					'html' => $parent_html
				];
				$hrefs[] = $href;
			}
		}

	}

	return $nodes;
}

/*	Получаем контент
*
*	$s - массив настроек
*	$url - адрес страницы
*	$pages - массив со списком страниц
*	$urls - массив со списком url'ов
*	$type_controle - ожидаемый тип данных (1 - пройдет только группа, 2 - только элемент)
*	$parent_html - код родителя, где найдена ссылка на узел
*	$parrent - id родительского раздела
*/
function _getUrls($s, $url, $pages = [], $urls = [], $type_controle = 0, $parent_html = '', $parrent = 0)
{
	//TRUE, если корневая страница каталога
	$basePage = $url == $s['start'];

	//Создаем папку для бекапа сайта
	if ($basePage)
	{
		mkdir($s['path'].'/local_backup', 0700);
		mkdir($s['path'].'/local_backup/pages', 0700);
		mkdir($s['path'].'/local_backup/parents_html', 0700);
	}

	//Подходит ли url
	if (!currentUrl($s, $url, $urls)) return array($pages, $urls);

	$html = Parser::getPage([
		'url' => $s['site'].$url
	]);

	if (empty($html['data'])) return array($pages, $urls);	//Если нет контента - завершаем

	$url = str_replace($s['site'], '', urldecode($html['data']['info']['url']) );
	if (!currentUrl($s, $url, $urls)) return array($pages, $urls);

	$content = $html['data']['content'];
	
	phpQuery::unloadDocuments();
	phpQuery::newDocument($content);

	//Если страница подходит под парсинг
	if ($basePage || pq( $s['pq']['group'] )->length() > 0 || pq( $s['pq']['item'] )->length() > 0)
	{
		//Название узла
		$name = '[название не найдено]';
		if ($basePage)
		{
			$name = 'Корень';
		}
		else
		{
			$n = pq( $s['pq']['title'] );
			if ($n && $n->text())
			{
				$name = trim($n->text());
			}			
		}

		//Тип узла
		if (pq( $s['pq']['item'] )->length() > 0)
		{
			$type = 2;		//элемент
		}
		elseif(!$basePage)
		{
			$type = 1;		//группа
		}
		else
		{
			$type = 0;		//корень
		}

		//Если тип соответсвует ожидаемому
		if ($type_controle == 0 || $type == $type_controle)
		{
			//Метатеги узла
			$title = pq('head title'); $title = $title->length() > 0 ? $title->text() : '';
			$description = pq('head meta[name="description"]'); $description = $description->length() > 0 ? $description->attr('content') : '';
			$keywords = pq('head meta[name="keywords"]'); $keywords = $keywords->length() > 0 ? $keywords->attr('content') : '';

			$pages[] = [
				'url' => $url,						//url страницы
				'name' => $name,					//название
				'pid' => $parrent,					//id родителя
				'type' => $type,					//тип узла (0 - корень, 1 - группа, 2 - элемент)
				'meta' => [
					'title' => $title,
					'description' => $description,
					'keywords' => $keywords
				]
			];

			$urls[] = $url;

			//Сохранение копии страницы с id.php
			$file_name = count($urls) - 1;
			$file_path = $s['path'].'/local_backup/pages/'.$file_name.'.html';
			Core_File::write($file_path, $content);
			$file_path = $s['path'].'/local_backup/parents_html/'.$file_name.'.html';
			Core_File::write($file_path, $parent_html);

			//Если НЕ на странице элемента - то ищем другие ссылки
			if ($type != 2)
			{
				//Сохраняем pid
				$pid = count($urls)-1;

				//Поиск ссылок на группы
				$nodes = searchLinks($s, $urls, pq( $s['pq']['groups_search'] ));
				foreach ($nodes as $node)
				{
					list($pages, $urls) = _getUrls($s, $node['href'], $pages, $urls, 1, $node['html'], $pid);
				}
				//Поиск ссылок на элементы
				$nodes = searchLinks($s, $urls, pq( $s['pq']['items_search'] ));
				foreach ($nodes as $node)
				{
					list($pages, $urls) = _getUrls($s, $node['href'], $pages, $urls, 2, $node['html'], $pid);
				}
			}
		}
	}

	return array($pages, $urls);
}


/*	Получаем контент
*
*	$s - массив настроек
*/
function getUrls($s)
{
	$file_path = $s['path'].'/local_backup/array.txt';
	$file_path_base = $s['path'].'/local_backup/array_base.txt';

	//Если есть локальная копия сайта - используем ее
	if (file_exists($file_path))
	{
		$pages = $urls = array();
		
		//Формируем структуру заново
		if (isset($_POST['get_structure']))
		{
		    $str = Core_File::read($file_path_base);
			$s['get_structure']	= TRUE;
		}
		//Используем текущую структуру
		else
		{
		    $str = Core_File::read($file_path);
		}
	
	    $file_arr = unserialize($str);
	    list($pages, $urls) = $file_arr;
	}

	else
	{
		//Получаем список страниц
		list($pages, $urls) = _getUrls($s, $s['start']);

		//Сохранение массива в файле
		$file_arr = [
			$pages,
			$urls
		];
		$str = serialize($file_arr);
		Core_File::write($file_path, $str);	
		Core_File::write($file_path_base, $str);	
		$s['get_structure']	= TRUE;
	}

	return array($s, $pages, $urls);
}