<?php
/*Контроль кэша изображений*/
preg_match_all('/"(\/[^"]*.(jpg|png|gif|svg|pdf))"/', $content, $imgs); //Поиск по изображений
if (count($imgs[0])>0)
{
	$imgs = array_unique($imgs[1]);
	foreach ($imgs as $img)
	{
		$content = str_replace($img, upcache($img), $content);
	}
}
?>