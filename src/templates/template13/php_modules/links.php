<?php
/*Замена пробелов в ссылках*/
	preg_match_all('/href="([^"]* [^"]*)/', $content, $hrefs);
	if (isset($hrefs[1]) && count($hrefs[1])>0)
	{
		foreach ($hrefs[1] as $href)
		{
			$content = str_replace_once($href, str_replace(' ', '%20', $href), $content); //Поиск и замена в html
		}
	}

	preg_match_all('/href="([^"]*&amp;nbsp;[^"]*)/', $content, $hrefs);
	if (isset($hrefs[1]) && count($hrefs[1])>0)
	{
		foreach ($hrefs[1] as $href)
		{
			$content = str_replace_once($href, str_replace('&amp;nbsp;', '%20', $href), $content); //Поиск и замена в html
		}
	}

/*noopener в target=_blank*/
	$content = str_replace('target="_blank"', 'target="_blank" rel="noopener noreferrer"', $content);
?>