<?php
/*
* !!!!!!!!!
* !!!!!!!!!
* !!!!!!!!!
* !!!!!!!!!
* !!!!!!!!! ПРЕДУСМОТРЕТЬ ПОВТОРНЫЙ ПАРСИНГ ФАЙЛОВ!!!!
* !!!!!!!!!
* !!!!!!!!!
* !!!!!!!!!
* !!!!!!!!!
* !!!!!!!!!
*/

//Модули для обработки html страниц
require('modules/getData.php');
$array = array(getCsv($s));

/*	Получаем массив для csv 1 страницы
*
*	$page - 1 страница
*
*/
function getPageArray($s, $page, $titles)
{
	//Получаем базовые поля
	$array_p = getPageArrayBase($page);	

	//Группа
	if ($page['type'] == 1)
	{
		
	}
	//Товар
	else
	{
		
	}
	
	return arrayConvert($array_p, $titles);
}


/*	Проход страниц в цикле
*
*	$pages - массив со списком страниц
*/
function importPages($s, $pages, $array, $pid = 0)
{
	foreach ($pages as $id => $page)
	{
		if ($id != 0 && $page['pid'] == $pid)
		{
			$page['id'] = $id;

			//Получаем массив для csv 1 страницы
			$array[$id] = getPageArray($s, $page, $array[0]);

			$array = importPages($s, $pages, $array, $id);
		}
	}
	return $array;
}
$array = importPages($s, $pages, $array);

//print_r($pages);
//print_r($array);


//Выводим сформированный csv на экран
showCsv($array);

//Сохраняем в файл
//saveCsv($s, $array);