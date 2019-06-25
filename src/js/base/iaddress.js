/*Поле адреса*/
var iaddress = document.querySelectorAll('.iaddress');
if (iaddress.length>0){
	
	var iaddress__list = [],
		iaddress__items = [],
		iaddress__li_select = [],
		iaddress__last = [],
		iaddress__timeout;

	//Добавляем список в html
		function iCreateList(i){
			iaddress__items[i] = '';
			iaddress__li_select[i] = -1;

			iaddress__list[i] = document.createElement('ol');
			iaddress__list[i].classList.add('iaddress-list');
			iaddress__list[i].i = i;

			iaddress[i].parentNode.insertBefore(iaddress__list[i], iaddress[i].nextSibling);

			iaddress__list[i].addEventListener('click', function(e){
				if (e.target.classList.contains('iaddress-list__item')){
					iAddEventSelect(this.i, e.target);
					iaddress[i].focus();
				}
			});

			iGetAddress(iaddress[i], 1000);	//Запускаем сразу поиск и проверку
		}

	//Получаем список адресов
		function iGetAddress(input, speed){
			window.clearTimeout(iaddress__timeout);
			iaddress__timeout = window.setTimeout(function(){
	
				var request = new XMLHttpRequest(),
					jsonData;

				jsonData = '{ "query": "'+input.value+'", "count": 5';

				//Если указаны ограничения по регионам
				if (iaddress_locations!=''){
					jsonData += ', "locations": '+iaddress_locations;
				}

				jsonData += '}';

				request.open('POST', 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', true);
				request.setRequestHeader('Content-Type', 'application/json');
				request.setRequestHeader('Accept', 'application/json');
				request.setRequestHeader('Authorization', 'Token 7ad3d9e4abbe5898c9e74ccfc803ee43db43e353');

				request.onload = request.onerror = function() {
					if (request.status >= 200 && request.status < 400){ 
						var data = JSON.parse(request.responseText);
						if (data.suggestions) iAddAddress(input.i, data.suggestions);
					}else{
						var errortext = request.responseText ? request.responseText : 'Попробуйте позже';
						console.error(errortext);
					}
				};

				request.send(jsonData);
	
			}, speed);
		}

	//Добавляем список адресов на страницу (id эл-та, данные)
		function iAddAddress(i, data){
			var new__list = document.createElement('ol');
				new__items = '',
				error_code = 0;

			//Заполняем виртуальный список
				data.forEach(function(item, j, arr) {
					new__items += '<li class="iaddress-list__item">'+item.value+'</li>';
				});
				new__list.innerHTML = new__items;

			//Получаем элементы списка
				if (data.length) {
					iaddress__li_select[i] = 0;
					iaddress__items[i] = new__list.querySelectorAll('.iaddress-list__item');
					iaddress__items[i][0].classList.add('iaddress-list__item_select');
				}else{
					iaddress__li_select[i] = -1;
					iaddress__items[i] = '';
				}


			//Переносим варианты в html
				iaddress__list[i].innerHTML = '';
				while (new__list.childNodes.length > 0) {
					iaddress__list[i].appendChild(new__list.childNodes[0]);
				}

			//Валидация
				if (data.length) {
					/* !!! ОСНОВНЫЕ ДАННЫЕ
					kladr_id + flat - уникальный id адреса (включая квартиру)

					country: "Россия"
					city: "Москва"
					city_with_type: "г Москва"
					region: "Москва" / region: "Московская"
					region_with_type: "г Москва" / region_with_type: "Московская обл"
					street_with_type: "ул Плещеева"
					house: "10"
					house_type: "д"
					*/
					/* !!! ЭТИХ ДАННЫХ МОЖЕТ НЕ БЫТЬ
					postal_code: "127560"
					settlement: "Фирсановка"
					settlement_with_type: "мкр Фирсановка"
					block: "1"		//корпус
					block_type: "к"
					flat: "10"		//квартира
					flat_type: "кв"
					*/

					//Если не хватает данных - выводим ошибку
						if (!data[0].data.house){
							error_code = 11;
						}
				}else{
					error_code = 10;
				}

				if (iaddress[i].error_code != error_code || iaddress[i].form__error_code){
					iaddress[i].error_code = error_code;

					if (iaddress[i].form__error_code>-1){ inputValidate(iaddress[i]); }
				}

		}

	//Вешаем обработчики событий (id input-а, новое значение)
		function iAddEventSelect(i, item){
			var val = item.innerHTML;
			iaddress[i].value = val;
			iGetAddress(iaddress[i], 0);
		}

	//Выбор из списка стрелками
		function iSelectNext(i, next){
			if (iaddress__items[i].length){
				var total = iaddress__items[i].length,
					select = iaddress__li_select[i];

				if (select > -1) iaddress__items[i][select].classList.remove('iaddress-list__item_select');

				select += next;

				if (select < 0){ select = total - 1; }
				else if (select >= total){ select = 0; }

				iaddress__items[i][select].classList.add('iaddress-list__item_select');

				iaddress__li_select[i] = select;
			}
		}

	for (var i = 0; iaddress[i]; i++) {
		iCreateList(i);
		iaddress[i].i = i;
		iaddress[i].error_code = 0;
		iaddress[i].setAttribute('autocomplete','off');	//отключаем автоподстановку html5

		//Фильтр адреса при вводе
			iaddress[i].addEventListener('input', function(){
				iGetAddress(this, 500);
			});


		//Выбор адреса стрелками
			iaddress[i].addEventListener('keydown', function(e){
				var next = 0,
					i = this.i;

				e = e || window.event;

				if (e.keyCode == '38'){ next = -1; }
				else if (e.keyCode == '40'){ next = 1; }

				if (next!=0) {
					e.preventDefault();
					iSelectNext(i, next);
				}else if (e.keyCode == '13') {
					e.preventDefault();
					if (iaddress__items[i][iaddress__li_select[i]]) {
						iAddEventSelect(i, iaddress__items[i][iaddress__li_select[i]]);
					}
				}
			});

		//Автоподстановка корректного адреса после ухода с поля
			iaddress[i].addEventListener('blur', function(){
				var i = this.i;
				if (!this.error_code && iaddress__items[i][0]) {
					iAddEventSelect(i, iaddress__items[i][0]);
				}
			});
	}
}