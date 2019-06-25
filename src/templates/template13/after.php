<?php
	$content = ob_get_contents(); ob_end_clean();
}

//Замена 1го вхождения подстроки
?>@@@include('php_modules/str_replace_once.php')<?php

//Контроль кэша изображений
?>@@@include('php_modules/img_cashe.php')<?php

//Обработка ссылок
?>@@@include('php_modules/links.php')<?php

//Добавление прелоадера
?>@@@include('php_modules/img_preloader.php')<?php

/*Если вкл. сжатие*/
	if ($htmlcompress)
	{
		preg_match_all('!(<(?:code|pre|script).*>[^<]+</(?:code|pre|script)>)!',$content,$pre); $content = preg_replace('!<(?:code|pre).*>[^<]+</(?:code|pre)>!', '#pre#', $content); $content = preg_replace('#<!–[^\[].+–>#', '', $content); $content = preg_replace('/[\r\n\t]+/', ' ', $content); $content = preg_replace('/>[\s]+</', '><', $content); $content = preg_replace('/[\s]+/', ' ', $content);if (!empty($pre[0])) {foreach ($pre[0] as $tag) {$content = preg_replace('!#pre#!', $tag, $content,1);}}$content = preg_replace('/<!--(?!<!)[^\[>].*?-->/', '', $content);
	}
/**/

//Форматирование заголовков
/*?>include('php_modules/title_format.php')<?php*/

//Блок картинок
?>@@@include('php_modules/imgblock.php')<?php

if (Core_Registry::instance()->get('ajax'))
{
	$aAjax['data'] = $content;
    echo json_encode($aAjax);
    exit();
}
else
{
	echo $content;	
} 

//$a=microtime(true)-$a; echo $a;

?>