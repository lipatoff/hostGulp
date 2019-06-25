<?php
/*
*	arrayConvert($array, $titles) - конвертирует массив для сохранения в csv
*	importMeta($meta) - метатеги
*/


/**********************************************/
/***  CSV  ************************************/
/**********************************************/

/*	Получить массив с заголовками из base.csv 
*
*	$s - настройки
*/
function getCsv($s)
{
	$file_name = $s['path'].'/base.csv';
	$f_csv = fopen($file_name, 'r');
	$array = fgetcsv($f_csv, 100, ';');
	fclose($f_csv);
	return $array;
}
/*	Сохранить csv для импорта
*
*	$s - настройки
*	$array - мультимассив данных
*/
function saveCsv($s, $array)
{
	$file_name = $s['path'].'/import.csv';
	$f_import = fopen($file_name, 'w');
	foreach ($array as $item)
	{
		fputcsv($f_import, $item, ';');
	}
	fclose($f_import);
}


/**********************************************/
/***  МАССИВ  *********************************/
/**********************************************/

/*	Выводим сформированный csv на экран
*
*	$array - мультимассив данных
*/
function showCsv($array)
{
	?><table><?php
	foreach ($array as $i => $item)
	{
		if ($i==0) echo '<thead>';
		$tag = $i>0 ? 'td' : 'th'; 
		?><tr><?php
			foreach ($item as $value)
			{
				echo '<'.$tag.'>';
				
				//Если значение - это ссылка
				if (substr($value, 0, 4)=='http' && strpos($value, '<')===false && strpos($value, '\n')===false)
				{
					$img_type = '.jpg.jpeg.png.gif.svg'; //форматы картинок
					$f_type = substr($value, strrpos($value,'.'));
					
					//Просто ссылка
					if (stripos($img_type, $f_type) === false)
					{
						echo '<a href="'.$value.'">'.$value.'</a>';
					}
					//Картинка
					else
					{
						echo '<div class="tableimg"><img src="'.$value.'"/></div>';
					}

				}
				else
				{
					echo $value;
				}

				
				echo '</'.$tag.'>';
			}
		?></tr><?php
		if ($i==0) echo '</thead><tbody>';
	}
	?></tbody></table>
	<form method="POST">
		<hr/>
		<input type="submit" name="go_parsing" class="button" value="Продолжить"/>
		<input type="hidden" name="step" value="2"/>
	</form>
	<form method="POST">
		<input type="submit" name="go_parsing" class="button button_two" value="Повторить"/>
		<input type="hidden" name="step" value="1"/>
	</form>
	<?php
}
/*	Конвертирует массив для csv
*
*	$array - массив для конвертации
*	$titles - массив заголовком из csv
*/
function arrayConvert($array, $titles)
{
	$new_array = $titles_array = array();

	foreach ($titles as $key => $value)
	{
		if (!isset($titles_array[$value])) $titles_array[$value] = $key;
		$new_array[$key] = '';
	}

	foreach ($array as $key => $value)
	{
		if (isset($titles_array[$key])) $key = $titles_array[$key];
		$new_array[$key] = $value;
	}

	return $new_array;
}


/**********************************************/
/***  ОБРАБОТКА HTML СТРАНИЦЫ  ****************/
/**********************************************/

/*	Базовая обработка полей страницы
*
*	$page - 1 страница
*/
function getPageArrayBase($page)
{
	$array_p = array();	

	//Группа
	if ($page['type'] == 1)
	{
		$array_p = array(
			'Название раздела' => $page['name'],
			'CML GROUP ID идентификатор группы товаров' => 'EXG_'.$page['id'],
			'CML GROUP ID идентификатор родительской группы товаров' => $page['pid']>0 ? 'EXG_'.$page['pid'] : 'ID00000000',
			'Описание раздела' => 'http://classtrip.ru/upload/shop_3/5/9/4/group_594/group_594.jpg',
			);	
	}
	//Товар
	else
	{
		$array_p = array(
			'Название товара' => $page['name'],
			'CML GROUP ID идентификатор группы товаров' => $page['pid']>0 ? 'EXG_'.$page['pid'] : 'ID00000000',
			'CML ID идентификатор товара' => 'EXI_'.$page['id'],
			);			
	}

	return $array_p;
}


/**********************************************/
/***  ФАЙЛЫ  **********************************/
/**********************************************/

/*	Получение полного адреса файла
*
*	$s - настройки
*	$page - адрес страницы
*	$url - адрес файла
*/
function getUrlFile($s, $page, $url)
{
	//Убираем хэш
		$url = explode('?', $url);
		$url = $url[0];

	//Формируем правильную ссылку на файл
		if (substr($url, 0, 1)=='/')	//Относительная ссылка от корня
		{
			$url = $s['site'].$url;
		}
		elseif (substr($url, 0, 4)!='http')	//Относительная ссылка от страницы
		{
			$url = $s['site'].$page.$url;
		}

	return $url;
}
/*	Сохранение файлов
*
*	$s - настройки
*	$url - адрес файла
*/
function downloadFile($s, $url)
{
	//Создаем папку для файлов
		$path = $s['path'].'/local_backup/upload';
		if (!file_exists($path)) mkdir($path, 0700);
		$path .= '/';

	//Копируем/формируем название файла
		$fname = substr($url, strrpos($url, '/')+1);
		if ($fname=='' || file_exists($path.$fname))
		{
			$pos = strrpos($fname, '.');
			$fname = substr($fname, 0, $pos).'__'.time().substr($fname, $pos);
		}

	//Сохраняем файл
	    $ReadFile = fopen($url, 'rb');
	    if ($ReadFile)
	    {
	        $WriteFile = fopen($path.$fname, 'wb');
	        if ($WriteFile)
	        {
	            while(!feof($ReadFile))
	            {
	                fwrite($WriteFile, fread($ReadFile, 4096 ));
	            }
	            fclose($WriteFile);
	        }
	        fclose($ReadFile);
	    }

	return $path.$fname;
}


/**********************************************/
/***  ДРУГОЕ  *********************************/
/**********************************************/

/*	Метатеги
*
*	$meta - массив с метатегами страницы
*/
function importMeta($meta)
{
	$title = $meta['title'];
	$description = $meta['description'];
	$keywords = $meta['keywords'];

	/*	
	//Удаляем шаблонные части метатегов
	$title = str_replace(' — ClassTrip', '', $title);
	$description = str_replace(' — ClassTrip', '', $description);
	$keywords = str_replace(' — ClassTrip', '', $keywords);
	*/

	return array($title, $description, $keywords);
}