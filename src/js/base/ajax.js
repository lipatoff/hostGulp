//Ajax-загрузка
var ajaxMore=document.querySelector('.ajax__more');

if (ajaxMore) {
	var ajaxMore__container = document.querySelector('.ajax__container'),
		ajaxMore_top = ajaxMore.getBoundingClientRect().top+pageYOffset, //top кнопки
		ajaxMore__hPage = document.documentElement.clientHeight, //Высота страницы
		ajaxMore__bPage,
		ajaxMore_part = ajaxMore.dataset.item ? false : true,	//TRUE - страница раздела, FALSE - страница элемента
		ajaxMore__step = ajaxMore_part ? +ajaxMore.dataset.step : 0,
		ajaxMore__id = ajaxMore_part ? '&group='+ajaxMore.dataset.group : '&item='+ajaxMore.dataset.item,
		ajaxMore__go = ajaxMore_part ? !ajax_lazy : true; //Выполнять ли ajax сразу

	function ajaxMore__scroll(){
		if (ajaxMore__go && pageYOffset+ajaxMore__hPage*1.5 > ajaxMore_top){
			ajaxMore.click();
		}		
	}

	function ajaxMore__resize(){
		if (ajaxMore__go){
			ajaxMore_top=ajaxMore.getBoundingClientRect().top+pageYOffset; //top эл-та
			ajaxMore__hPage=document.documentElement.clientHeight; //Низ окна
			ajaxMore__scroll();
		}
	}

	ajaxMore.addEventListener('click', function(){
		var	formData='next='+ajaxMore__step+ajaxMore__id,
			request = new XMLHttpRequest();

		if (ajaxMore_part){
			formData += '&offset='+ajaxMore.dataset.offset;
		}
		
		ajaxMore__go = false;
		ajaxMore.classList.add('button_send');

		request.open('POST', window.location, true);
		request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

		request.onload = function() {
			if (request.status >= 200 && request.status < 400) {
				if (ajaxMore_part){
					ajaxMore.dataset.offset = ajaxMore__step+Number(ajaxMore.dataset.offset);
				}
				var new__data = JSON.parse(request.responseText),
					new__html = document.createElement('div');
				
				new__html.innerHTML = new__data.data;

				new__html = ajax_functions(new__html);

				while (new__html.childNodes.length > 0) {
					ajaxMore__container.appendChild(new__html.childNodes[0]);
				}

				if (new__data.last){
					ajaxMore.classList.add('ajax__more_last');
					window.removeEventListener('scroll', ajaxMore__scroll);
					window.removeEventListener('resize', ajaxMore__resize);
				}else{
					ajaxMore.classList.remove('button_send');
					ajaxMore__go=true;
					ajaxMore_top = ajaxMore.getBoundingClientRect().top+pageYOffset;
				}

			} 
			else { alert('Возникла ошибка! Попробуйте позже.');	}
		};
		request.onerror = function() { alert('Возникла ошибка! Попробуйте позже.'); };

		request.send(formData);

		return false;
	});

	window.addEventListener('scroll', ajaxMore__scroll);
	window.addEventListener('resize', ajaxMore__resize);
}