<?php
/*	Формируем структуру
*
*	$s - массив настроек
*	$pages - массив со списком страницы
*	$urls - массив со списком url'ов
*/
function _getStructure($s, $pages, $urls)
{
	switch ($s['type'])
	{
		//Без структуры
		case 0:
			foreach ($pages as $i => $page)
			{
				$pages[$i]['pid'] = 0;
			}
			break;
		
		//По url
		case 1:
			foreach ($pages as $i => $page)
			{
				$pages[$i]['pid'] = 0;
				$parent_url = substr($page['url'], 0, strrpos(substr($page['url'],0,-1), '/')).'/'; //Отсекаем последний раздел в url

				if (in_array($parent_url, $urls))
				{
					$pid = array_search($parent_url, $urls);
					$pages[$i]['pid'] = $pid > 0 ? $pid : 0;
				}
			}

			break;
			
			
		//По хлебным крошкам
		case 2:	
			foreach ($pages as $i => $page)
			{
				$pages[$i]['pid'] = 0;
				if ($s['pq']['breadcrumbs']!='')
				{

					//Открывает html-код страницы
					$file_path = $s['path'].'/local_backup/pages/'.$i.'.html';
					if (file_exists($file_path))
					{
						$content = Core_File::read($file_path);
						phpQuery::unloadDocuments();
						phpQuery::newDocument($content);

						//Ищем селектор хлебных крошек
						$pqbread = pq( $s['pq']['breadcrumbs'] );
						if ($pqbread->length() > 0)
						{
							//Получаем все ссылки
							$pqlinks = $pqbread->find('a');
							$links = array();
							foreach ($pqlinks as $pqlink)
							{
								$pqlink = pq($pqlink);
								$links[] = trim($pqlink->attr('href'));
							}

							//Ищем с конца ссылку на родителя
							$links = array_reverse($links);
							$parent_url = '/';
							foreach ($links as $link)
							{
								if ($link != $urls[$i])
								{
									$parent_url = $link;
									break;
								}
							}

							//Получаем id родителя
							if ($parent_url!='/')
							{
								$pid = array_search($parent_url, $urls);
								if ($pid>0) $pages[$i]['pid'] = $pid;
							}
						}
					}
				}
			}
			break;
	}

	return $pages;
}


/*	Обновление структуры
*
*	$pages - массив со списком страниц
*	$urls - массив со списком url'ов
*/
function updateStructure($pages_old, $urls_old)
{
	$pages = $pages_old;
	$urls = $urls_old;

	if (isset($_POST['pages']))
	{
		$pages = array($pages_old[0]);
		$urls = array($urls_old[0]);

		$pages_id = $_POST['pages'];
		foreach ($pages_id as $id)
		{
			if (isset($pages_old[$id]))
			{
				//Обновляем pid
				if (   isset($_POST['pid_'.$id])						//если есть POST переменная
					&& ($_POST['pid_'.$id] || $_POST['pid_'.$id]==0)	//если она не пустая
					&& isset($pages_old[$_POST['pid_'.$id]]) 			//если есть родительский узел
					&& $pages_old[$_POST['pid_'.$id]]['type']!=2		//если он - раздел
					)
				{
					$pages_old[$id]['pid'] = $_POST['pid_'.$id];
				}

				//Заполняем новый массив страниц и урлов
				$pages[$id] = $pages_old[$id];
				$urls[$id] = $urls_old[$id];
			}
		}

		//Обнуляем родителей, если они удалены
		foreach ($pages as $id => $page)
		{
			if (!isset($pages[$pages[$id]['pid']]))
			{
				$pages[$id]['pid'] = 0;
			}
		}
	}

	return array($pages, $urls);
}


/*	Формируем структуру
*
*	$s - массив настроек
*	$pages - массив со списком страниц
*	$urls - массив со списком url'ов
*/
function getStructure($s, $pages, $urls)
{
	$save = FALSE;

	//Сброс структуры
	if (isset($s['get_structure']) && $s['get_structure'])
	{
		$pages = _getStructure($s, $pages, $urls);
		$save = true;
	}
	//Обновление структуры
	elseif (isset($_POST['update_structure']))
	{
		list($pages, $urls) = updateStructure($pages, $urls);
		$save = true;
	}

	//Сохранение массива в файле
	if ($save)
	{
		$file_path = $s['path'].'/local_backup/array.txt';
		$file_arr = [
			$pages,
			$urls
		];
		$str = serialize($file_arr);
		Core_File::write($file_path, $str);
	}

	return $pages;
}


/*	Выводим структуру
*
*	$pages - массив со списком страниц
*/
function _showStructure($s, $pages, $pid = 0)
{
	?>
	<ul class="items"><?php
	foreach ($pages as $id => $page)
	{
		if ($id != 0 && $page['pid'] == $pid)
		{
			?><li class="items__li">
				<div class="items__item item">
					<input class="item__on" type="checkbox" name="pages[]" value="<?=$id;?>" checked/>
					<a class="item__link" href="<?=$s['site'].$page['url']; ?>" target="_blank"><?php echo $page['name']; ?></a>
					<small class="item__small"> <?=$id;?></small>
					<?php if ($page['type'] != 2) {?>
						<small class="item__small">&#10065;</small>
					<?php } ?>
					<input class="hide" type="checkbox" id="edit<?=$id;?>"/>
					<div class="edit">
						<label for="edit<?=$id;?>" class="edit__icon">&#9998;</label>
						<div class="edit__open">
							<small>pid: </small><input class="edit__pid" type="number" name="pid_<?=$id;?>" />
						</div>
					</div>
					<div class="item__meta">
					<?php 
						foreach ($page['meta'] as $key => $value)
						{
							if ($value != '')
							{
								echo '<p>'.$key.': '.$value.'</p>';
							}
						}
					?>
					</div>
				</div>

				<?php _showStructure($s, $pages, $id); ?>
			</li><?php
		}
	}
	?></ul><?php
}

function showStructure($s, $pages)
{
	?><form method="POST">
		<?php _showStructure($s, $pages); ?>
		<hr/>
		<input type="submit" name="update_structure" class="button" value="Обновить структуру"/>
	</form>
	<form method="POST">
		<input type="submit" name="get_structure" class="button button_two" value="Сбросить"/>
	</form>
	<?php
}
