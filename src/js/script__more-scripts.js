//Подключение внешних скриптов
if (typeof userAuth == 'undefined') { //Если пользователь не авторизован
	var script = document.createElement('script');
	script.src = 'SRC СКРИПТА';
	document.getElementsByTagName('head')[0].appendChild(script);
}