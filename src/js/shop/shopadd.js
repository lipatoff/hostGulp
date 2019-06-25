/*Кнопка добавления (в сравнение, избранное и т.д.)*/


/*	Событие кнопки добавления
*	--------------------------------------
*	Принимает: e - событие
*/
function shopAddEvent(e){
	var button = this,
		link = shop_link+'?'+this.dataset.action,
		request = new XMLHttpRequest();

	e.preventDefault();

	if (!button.classList.contains('shopadd_current')) link += '&add=1';

	request.open('GET', link);
	request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

	request.onload = function() {
		if (request.status >= 200 && request.status < 400) {
			var data = JSON.parse(request.responseText);
			if (data.do) button.classList.toggle('shopadd_current');		//Переключаем значение
			if (data.name && 'count' in data) littleVal(data.name, data.count);	//Обновляем кол-во
		}
	};

	request.send();
}

/*	Обработка кнопки добавления
*	--------------------------------------
*	Принимает: html - документ
*/
function shopAdd(html){
	var el = html.querySelectorAll('.shopadd');
	for (var i = 0; el[i]; i++) {
		el[i].addEventListener('click', shopAddEvent);
	}
}

shopAdd(document);