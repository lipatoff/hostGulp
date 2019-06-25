<?php
$file_csv = $s['path'].'/base.csv';
$file_cols = $s['path'].'/local_backup/titles.txt';

/* Формируем файл с массивом полей */
function parCsv($file_csv, $file_cols)
{
	//$str = Core_File::read($file_csv);
	$csv = fopen($file_csv, 'r');
	$array = fgetcsv($csv, 100, ';');
	ob_start();
	print_r($array);
	$str = ob_get_contents();
	ob_end_clean();	
	Core_File::write($file_cols, $str);
}


//Если нет файлов
if (!file_exists($file_csv))
{
	?>
	<hr/>
	<p>Для начала парсинга сайта:<br/>
	1. Поместите файл 'base.csv' в корень этого раздела (формат UTF-8, разделитель точка с запятой)<br/>
	2. Сделайте полную резервную копию сайта<br/>
	</p>
	<?php
}
elseif (!file_exists($file_cols))
{
	?>
	<hr/>
	<p>Для начала парсинга сайта:<br/>
	В соответствеии со списком полей в '/local_backup/titles.txt' настройте файл импорта 'import.php'<br/>
	</p>
	<?php
	parCsv($file_csv, $file_cols);

}
//Если парсинг еще не начался
elseif (!isset($_POST['go_parsing']))
{
	?>
	<hr/>
	<form method="POST">
		<input type="submit" name="go_parsing" class="button" value="Парсинг сайта"/>
		<input type="hidden" name="step" value="1"/>
		<p>ВНИМАНИЕ! Перед парсингом сделайте полную резервную копию сайта!</p>
		<p>В соответствеии со списком полей в '/local_backup/titles.txt' настройте файл импорта 'import.php'</p>
	</form>
	<?php
}
//Процесс парсинга
else
{
	require('import.php');
}