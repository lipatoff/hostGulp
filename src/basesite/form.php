<?php
function file_upload($file_name, $file_size=1){
	$path_upload = '/upload/forms/'; //Папка загрузки
	$err = false;
	if(is_uploaded_file($_FILES[$file_name]['tmp_name'])){
		$root_upload = $_SERVER['DOCUMENT_ROOT'].$path_upload;
		$formats = ' image/jpeg image/png image/gif application/vnd.openxmlformats-officedocument.wordprocessingml.document application/vnd.openxmlformats-officedocument.spreadsheetml.sheet application/msword application/vnd.ms-excel application/pdf application/octet-stream'; //форматы файлов
		$formatstype = '.jpg.jpeg.png.gif.docx.doc.xlsx.xls.rtf.pdf.rar.zip'; //форматы файлов
		$f_type = 'type';
		$f_name = $_FILES[$file_name]['name'];

		if (strlen($f_name)>4) $f_type = substr($f_name, strrpos($f_type,'.'));
		$f_name = date('mdyHis').$f_type; $f_name = basename($f_name);

		if ($_FILES[$file_name]['size']>1024*1024*$file_size){ $err = 'Размер файла больше '.$file_size.' мб'; }
		elseif ((stripos($formats, $_FILES[$file_name]['type']) === false) || (stripos($formatstype, $f_type) === false) ){ $err = 'Неверный формат файла'; }
		elseif(move_uploaded_file($_FILES[$file_name]['tmp_name'], $root_upload.$f_name)) {
			$link_file = isset($_SERVER['HTTP_X_HTTPS']) ? 'https://' : 'http://';
			$link_file .= $_SERVER['HTTP_HOST'].$path_upload.$f_name;
		}
		else{$err='Ошибка загрузки файла';}

	}else{$err=true;}

	return $err ? false : '<a href="'.$link_file.'">Открыть</a>';
}

if ($_POST['js']){ 
	$omess=''; if (isset($_POST['omess'])){ $omess = substr(htmlspecialchars(trim($_POST['omess'])), 0, 1000000); }

	if (strpos(' '.$omess, 'http://') || strpos(' '.$omess, 'https://')) exit();  

	$t = array();
	/*---------------------  АДРЕСА  ---------------------*/
	//Кому отправляем 
	$to = 'dima@seo-eng.ru'; //
	//От кого 
	$from='info@yandex.ru';

	/*-------------------  ПОЛЯ ФОРМЫ  -------------------*/
	//Имя
	if (isset($_POST['oname'])){ $t['Имя отправителя']=substr(htmlspecialchars(trim($_POST['oname'])), 0, 100); }
	//Телефон
	if (isset($_POST['otel'])){ $otel = substr(htmlspecialchars(trim($_POST['otel'])), 0, 30); if ($otel!='') {$t['Телефон']='<a href="tel:'.$otel.'">'.$otel.'</a>';} }
	//E-mail
	if (isset($_POST['omail'])){ $t['E-mail']=substr(htmlspecialchars(trim($_POST['omail'])), 0, 100); }
	//Комментарий
	if ($omess!='') { $t['Комментарий']=$omess; }
	//Другие данные
	//if (isset($_POST['otitle'])){ $t['Занятие']=substr(trim($_POST['otitle']), 0, 400); }
	
	/*-------------------  ЗАГРУЗКА ФАЙЛОВ  -------------------*/
	/*
	$ofile=file_upload('ofile',2);
	if ($ofile) { $t['Файл']=$ofile; }
	else { ECHO $ofile; exit(); }

	/*-------------------  ТИПЫ ПИСЕМ  -------------------*/
	switch ($_POST['otype']) {
		case '1': 	$head='Обратный звонок с сайта'; 
					$hellow='Здравствуйте! Поступил заказ обратного звонка с сайта!'; 
					break;         
/*      case '2':	$head='Запись на занятие'; 
					$hellow='Здравствуйте! Поступила новая заявка на запись!'; 
					break;
*/      default:  	$head='Сообщение с сайта'; 
					$hellow=''; 
					break; 
	  }

	/*-------------------  ОПИСАНИЕ  -------------------*/
	if (isset($_POST['ourl']))
	{
		$desctiprion .= '<h3>Страница</h3>';
		if (isset($_POST['oh1'])) { $desctiprion .= '<p>'.substr(htmlspecialchars(trim($_POST['oh1'])), 0, 100).'</p>'; }
		$desctiprion .= '<a href="'.$_POST['ourl'].'">'.trim($_POST['ourl']).'</a>';
	}

	$desctiprion .= '<h3>Данные отправителя</h3>';

	/*--------------- ФОРМИРОВАНИЕ ПИСЬМА  ---------------*/
	$mess = '<html><body><p>'.$hellow.'</p>'.$desctiprion.'<table border="1" cellpadding="10" cellspacing="0" bordercolor="#aaa"><tbody>';
	foreach ($t as $key => $value) {
		if ($value!=''){
			$mess.='<tr><td style="background-color:#eee">'.$key.'</td><td>'.$value.'</td></tr>';
		}
	}
	$mess.='</tbody></table>';

	if (isset($_POST['oassent'])) {$mess.='<p>Отправитель дал согласие на обработку персональных данных.</p>';}
	
	$mess.='</body></html>';

	/*--------------------  ОТПРАВКА  --------------------*/
	$head = "=?utf-8?b?".base64_encode($head)."?=";
	$headers = "From: $from\r\nReply-to:$from\r\nContent-type:text/html;charset=utf-8\r\n";
	if (mail($to, $head, $mess, $headers)) ECHO 'success';
}