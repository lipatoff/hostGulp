/*Страница корзины*/
if (cart){
	var cart__total,	//сумма всех товаров
		js_cart__total,

		cart__count,	//кол-во товара
		js_cart__count,
		
		cartItems = cart.querySelectorAll('.cart-item_active'),	//товары

		cartItems__count = [],	//кол-во 1 товара
		js_cartItems_count = [],

		js_cartItems_postpone = [], //Товар в отложенных
		js_cartItems_server = [], //Массив - Состояние товара (кол-во отложен)

		cartItems__total = [],	//сумма 1 товара

		cart__submit = cart.querySelector('.cart__submit'),	//кнопка Далее
		
		cart__form = document.querySelector('form.cart, .cart form'),	//Форма отправки корзины

		cart__cartinfo = cart.dataset.cartinfo,	//Контрольная сумма для синхронизации

		js_cart__timeout;	//будущая функция timeout

	//Обновление данных в корзине динамически
		function cartUpdateTotalDynamic(i, count){
			var price = cartItems[i].dataset.price,
				j = 1;	//1 = добавляем / -1 = откладываем

			//Отложить / добавить
			if (count==-1){
				if (js_cartItems_postpone[i]) j = -1;
				
				//Считаем общее кол-во
					js_cart__count += j * js_cartItems_count[i];
					js_cart__total += j * js_cartItems_count[i] * price;
			}

			//Смена цены
			else{
				//Считаем общее кол-во
					if (!js_cartItems_postpone[i]){
						js_cart__count += count - js_cartItems_count[i];
						js_cart__total += (count - js_cartItems_count[i]) * price;
					}

				js_cartItems_count[i] = count;
				cartItems__total[i].innerHTML = priceFormat(price * count);
			}

			//Обновляем общее кол-во
				if (count==-1 || !js_cartItems_postpone[i]){
					cart__count.innerHTML = priceFormat(js_cart__count);
					cart__total.innerHTML = priceFormat(js_cart__total);
				}
		}

	//Обновление данных в корзине на сервере
		function cartUpdateTotal(speed){
			window.clearTimeout(js_cart__timeout);
			js_cart__timeout = window.setTimeout(function(){
				var formData = new FormData(),
					go = false;	//нужно ли отправлять данные на сервер
					
				for (var i = 0; js_cartItems_count[i]; i++) {	//Перебираем все товары
					if (js_cartItems_server[i] != js_cartItems_count[i]+' '+js_cartItems_postpone[i]){
						//Если инфо о товаре изменилось - отправим новое на сервер
							formData.append(cartItems__count[i].name, js_cartItems_count[i]);
							if (js_cartItems_postpone[i]) formData.append('postpone_'+cartItems[i].dataset.id, 1);

						js_cartItems_server[i] = js_cartItems_count[i]+' '+js_cartItems_postpone[i];
						go = true;
					}
				}

				if (go){
					formData.append('recount', 1);
					formData.append('cartinfo', cart__cartinfo);
					cartUpdate(formData, '', false, false);
				}
			}, speed);
		}

	//Обновить кнопку Далее
		function cartResetButton(){
			var submit_show = false;

			for (var i = 0; js_cartItems_count[i]; i++) {
				if (js_cartItems_count[i]>0 && !js_cartItems_postpone[i]) {
					submit_show = true;
					break;
				}
			}

			if (submit_show) cart__submit.classList.remove('hide');
			else cart__submit.classList.add('hide');
		}

	//Отложить товар
		function cartPostpone(i){
			js_cartItems_postpone[i] = !js_cartItems_postpone[i];
			cartUpdateTotalDynamic(i, -1); //Обновление Итого
			cartUpdateTotal(500);
			cartResetButton();
		}

	//Настройка товаров
		function cartItemSettings(i){
			//Обновить данные
			if (cartItems__total[i]){
				count = cartItems__count[i].value;

				/*
				console.log('OLD---------------------------');
				console.log('itemCount '+js_cartItems_count[i]);
				console.log('price '+cartItems[i].oldprice);
				console.log('js_cart__count '+js_cart__count);				
				console.log('js_cart__total '+js_cart__total);
				*/

				price = cartItems[i].dataset.price;
				//Обновляем ИТОГО
				js_cart__count += count - js_cartItems_count[i];
				js_cart__total += count * price - js_cartItems_count[i] * cartItems[i].oldprice;
				//Обновление цен
				js_cartItems_count[i] = count;
				cartItems__total[i].innerHTML = priceFormat(price * count);

				/*
				console.log('-----------------------------');
				console.log('itemCount '+count);
				console.log('price '+price);				
				console.log('js_cart__count '+js_cart__count);
				console.log('js_cart__total '+js_cart__total);
				*/
			}
			//Первичная настройка
			else{
				cartItems__count[i] = cartItems[i].querySelector('.cart-item__count'),	//кол-во 1 товара	
				cartItems__total[i] = cartItems[i].querySelector('.cart-item__total'),	//сумма 1 товара

				cartItems__count[i].jsid = i;
				js_cartItems_count[i] = cartItems__count[i].value;
				js_cartItems_postpone[i] = cartItems[i].classList.contains('cart-item_postpone');
			}
			
			js_cartItems_server[i] = js_cartItems_count[i]+' '+js_cartItems_postpone[i];
		}

	//Подсчет строки Итого
		function cartCalculateTotal(){
			cart__total = cart.querySelector('.cart__total');	//сумма всех товаров
			cart__count = cart.querySelector('.cart__count');	//кол-во товара

			js_cart__count = 0;
			js_cart__total = 0;
			
			for (var i = 0; cartItems[i]; i++) {
				if (!js_cartItems_postpone[i]){
					js_cart__count += +js_cartItems_count[i];
					js_cart__total += cartItems[i].dataset.price*js_cartItems_count[i];
				}
			}
		}

	for (var i = 0; cartItems[i]; i++) {
		cartItems[i].jsid = i;

		cartItemSettings(i);

		cartItems[i].addEventListener('change', function(e){
			if (e.target.classList.contains('cart-item__postpone')){
				if (e.target.checked){ cart__cartinfo = cart__cartinfo.replace('.-'+this.dataset.id+'.', '.'+this.dataset.id+'.'); }
				else{ cart__cartinfo = cart__cartinfo.replace('.'+this.dataset.id+'.', '.-'+this.dataset.id+'.'); }
				this.classList.toggle('cart-item_postpone');
				cartPostpone(this.jsid);
			}
		});
	}
	cartCalculateTotal();


	cart.addEventListener('input', function(e){
		if (e.target.classList.contains('cart-item__count')){
			var el = e.target;
				i = el.jsid,
				count = el.value;
			
			if (+count>+el.max){
				count=el.max;
			}else if (+count<+el.min){
				count = el.min;
			}

			if (js_cartItems_count[i] != count){ //Обновляем Итого
				cartUpdateTotalDynamic(i, count);
				if (!js_cartItems_postpone[i]) cartUpdateTotal(500);
			}
		}
	});

	window.onbeforeunload = function() {cartUpdateTotal(0);}

	//Обновление данных в форме после ajax (принимает data.data)
	function cartUpdateFormInfo(data){

		var html = document.createElement('div');
		html.innerHTML = data;

		//Обновление товаров
			var el = html.querySelectorAll('.cart-item'),	//товар ajax
				cart__el,	//товар в корзине
				el__count,	//кол-во товара ajax
				remove,		//нужно ли обновить инфо о товаре
				jsid;

			for (var i = 0; el[i]; i++) {
				cart__el = cart.querySelector('[data-id="'+el[i].dataset.id+'"]');

				jsid = cart__el.jsid;
				remove = false;

				//Обновление остатков товара
					el__count = el[i].querySelector('.cart-item__count');
					if (el__count){

						if (cartItems__count[jsid].value != el__count.value && !cartItems__count[jsid].matches(':focus')){
							//console.dir(document.activeElement);
							remove = true;
							cartItems__count[jsid].value = el__count.value;
						}
						if (cartItems__count[jsid].max != el__count.max){
							cartItems__count[jsid].max = el__count.max;
							cartItems__count[jsid].serror.innerHTML = 'макс: '+el__count.max;
						}
						scountValidate(cartItems__count[jsid]);
					}

				//Обновление цен
					cart__el.oldprice = cart__el.dataset.price;
					if (cart__el.dataset.price != el[i].dataset.price) {
						remove = true;
						cart__el.dataset.price = el[i].dataset.price;
						cart__el.querySelector('.cart-item__price').innerHTML = el[i].querySelector('.cart-item__price').innerHTML;
					}

				if (remove) {
					cartItemSettings(jsid);
				}
			}

		//Обновление ajax-блоков
			var el = html.querySelectorAll('[data-ajax]'),
				cart__el;
			for (var i = 0; el[i]; i++) {
				cart__el = cart.querySelector('[data-ajax="'+el[i].dataset.ajax+'"]');
				if (cart__el && cart__el.innerHTML !=el[i].innerHTML) {
					cart__el.innerHTML = el[i].innerHTML;
					imgLazy(cart__el);
				}
			}

			cartCalculateTotal();

		//goals_form(0);
	}


	//Форма отправки корзины
	cart__form.setAttribute('novalidate', 'novalidate'); //Отключаем html валидацию
	cart__form.addEventListener('submit', function(e){

		var form=this,
			elements=form.elements,
			el;

		for (var i = 0; elements[i]; i++) {
			if (elements[i].type=='number'){
				el = elements[i];
				if (+el.value<+el.min || isNaN(parseInt(el.value))){
					el.value=el.min;
				}else if (+el.value>+el.max){
					e.preventDefault();
				}
			}
		}

		//Проверка остатков

	//	e.preventDefault();
	});

}