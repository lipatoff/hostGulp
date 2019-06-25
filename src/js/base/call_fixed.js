//Убираем косяк с целевыми звонками
var tel = document.querySelectorAll('a[href^="tel:"]');
if (tel.length>0){
	for (var i = 0; tel[i]; i++) {
		var href = parseInt('0'+tel[i].href.replace(/\D+/g,'')),
			text = parseInt('0'+tel[i].text.replace(/\D+/g,''));

		//Если номера разные - то меняем адрес ссылки
		if (href!=text){
			tel[i].href = 'tel:+'+text;
		}
		
	}
}
