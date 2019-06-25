<?php
/*Форматирование заголовков*/
preg_match_all('/<h[23][^>]*>([^><]* [^><]*)<\/h[23]>/', $content, $h23); //Поиск по тегу
//preg_match_all('/class="([^"]* )?h1[" ][^>]*>([^><]* [^><]*)<\//', $content, $h1); //Поиск по классу

if (count($h23[0])>0)
{
	$headers = $h23[0]; //$headers = array_merge($h1[0], $h3[0]);
	$htexts = $h23[1];  //$htexts = array_merge($h1[2], $h3[1]);

	foreach ($htexts as $i => $htext)
	{
		$text = str_replace('&nbsp;', '|', $htext); //Замена неразрывных пробелов
		$half = floor(strlen($text)/2);				//Половина длинны строки
		$first = substr($text, 0, $half);			//1 половина
		$second = substr($text, $half); 			//2 половина
		$first = strrpos($first, ' ') === false ? 0 : strrpos($first, ' ');			//Пробел в 1 половине
		$second = strpos($second, ' ') === false ? 999 : strpos($second, ' ')+1;	//Пробел во 2 половине

		$hnew = ($half-$first < $second) 			//Замена пробела на <br/>
			? substr($text, 0, $first).'<br/>'.substr($text, $first+1)
			: substr($text, 0, $half+$second-1).'<br/>'.substr($text, $half+$second);

		//Уменьшение длинных заголовков
			$first = stristr($hnew, '<br/>', TRUE);
			$second = substr(stristr($hnew, '<br/>'), 5);
			if (mb_strlen($first)>16) $first = '<span class="small">'.$first.'</span>';
			if (mb_strlen($second)>16) $second = '<span class="small">'.$second.'</span>';
			$hnew = $first.'<br/>'.$second;

		$hnew = str_replace('|', '&nbsp;', $hnew);	//Возврат неразрывных пробелов
		$content = str_replace($headers[$i], str_replace($htext, $hnew, $headers[$i]), $content); //Поиск и замена в html
	}
}
?>