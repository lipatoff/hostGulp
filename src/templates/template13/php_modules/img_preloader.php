<?php
/*Добавление прелоадера*/
$content = preg_replace('/(<img)(((?! src=|>).)*>)/', '$1 src="/images/preloader.svg"$2', $content);
?>