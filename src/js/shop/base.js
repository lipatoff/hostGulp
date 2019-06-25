/*ИМ*/
var cart = document.querySelector('.cart');

//Форматирование цен
function priceFormat(e){
	return e.toLocaleString();
}

//Валидация количества
function scountValidateInput(el){
	el.value = el.value.replace(/\D+/g,"");
	
	if (el.value.length>el.max.length+1) el.value=el.value.substr(0,el.max.length+1);

	if (+el.value>+el.max){
		el.serror.classList.remove('hide');
	}else{
		el.serror.classList.add('hide');
	}
}

function scountValidate(el){
	//Если элемент уже был на странице
	if (!el.serror){
		/*Errors span*/
			el.serror = document.createElement('span');
			el.serror.classList.add('error');
			el.serror.classList.add('scount__error');
			el.serror.classList.add('hide');
			el.serror.innerHTML = 'макс: '+el.max;
			el.parentNode.appendChild(el.serror);
		/**/

		el.addEventListener('input', function(){
			scountValidateInput(this);
		});

		el.addEventListener('keypress', function(e){
			if (!(e.key.search(/^[0-9]*$/)+1)) {
				e.preventDefault();
			}
		});		

		el.addEventListener('blur', function(){
			if (+this.value<+this.min || isNaN(parseInt(this.value))){
				this.value=this.min;
			}else if (+this.value>+this.max){
				this.value=this.max;
				this.serror.classList.add('hide');
			}
		});
	}
	
	scountValidateInput(el);
}


//Обновление корзины (форма, адрес, кнопка)
var littleCart = document.querySelector('.little-cart');

function cartUpdate(formData, url, button, container){
	var request = new XMLHttpRequest();

	if (button) button.classList.add('button_send');
	formData.append('js', '1');

	if (cart) formData.append('incart', '1');
		
	request.open('POST', url, true);

	request.onload = request.onerror = function() {
		if (request.status >= 200 && request.status < 400){ 
			var data = JSON.parse(request.responseText);

			if (data.action!=''){
				if (cart){	//Корзина
					cartUpdateFormInfo(data.data);
				}
				else if (littleCart){ //Краткая корзина
					littleCart.innerHTML = data.data;

					if ('count' in data) littleVal('cart', data.count);	//Обновляем кол-во

					imgLazy(littleCart); 	//Ленивая загрузка изображений
					//goals_form(0);
					delToCart(littleCart);
				}
			}

			//Надо перезагрузить страницу
			if (data.action=='reload'){
				window.location.reload();
			//Товар добавлен в корзину
			}else if (data.action=='added'){
				if (data.error){container.classList.add('addcart_noactive');}	//не добавлен
				else {container.classList.add('addcart_incart');}
			//Товар удален из корзины
			}else if (data.action=='deleted'){
				container.classList.add('delcart_hide');
				if (container.classList.contains('cart-item_active')) { //Обновляем Итого, если в корзине
					cartUpdateTotalDynamic(container.jsid, 0);
					js_cartItems_server[i] = '0 '+js_cartItems_postpone[container.jsid];
					cartResetButton();
				}
			}
		}else{
			var errortext = request.responseText ? request.responseText : 'Попробуйте позже';
			console.error(errortext);
		}

		if (button) button.classList.remove('button_send');
	};

	request.send(formData);
}


//Добавление в корзину
function addToCart(html){
	var addcart = html.querySelectorAll('.addcart'),
		addcart__form;

	if (addcart.length>0){
		for (var i = 0; addcart[i]; i++) {
			addcart__form = addcart[i].querySelector('.addcart__form');

			if (addcart__form){

				addcart__form.setAttribute('novalidate', 'novalidate'); //Отключаем html валидацию

				/*Отправка формы*/
					addcart[i].addEventListener('submit', function(e){
						e.preventDefault();

						var elements=e.target.elements,
							element_val,
							button_submit,
							formData = new FormData(),
							el;

						for (var j = 0; elements[j]; j++) {
							el = elements[j];
							if (el.type == 'submit'){
								button_submit = el;
							}else{
								element_val=parseInt(elements[j].value);
								if (el.type=='number'){
									if (+el.value<+el.min || isNaN(parseInt(el.value))){
										el.value=el.min;
									}else if (+el.value>+el.max){
										el.value=el.max;
									}
								}
								formData.append(el.name, el.value);
							}
						}

						cartUpdate(formData, e.target.action, button_submit, this);
					});
			}
		}
	}

	var el = html.querySelectorAll('.scount__input');
	for (var i = 0; el[i]; i++) {
		scountValidate(el[i]);
	}
}
addToCart(document);


//Удаление из корзины
function delToCart(html){
	var delcart = html.querySelectorAll('.delcart');
	if (delcart.length>0){
		for (var i = 0; delcart[i]; i++) {
			delcart[i].addEventListener('click', function(e){
				if (e.target.classList.contains('delcart__button'))
				{
					e.preventDefault();
					var delcart__id = e.target.search.match(/\?delete=([0-9]*)/);
					if (delcart__id && delcart__id[1])
					{
						var formData = new FormData();

						formData.append('delete',delcart__id[1]);

						if (cart && this.classList.contains('cart-item')) {
							cart__cartinfo = cart__cartinfo.replace('.'+this.dataset.id+'.', '.');
							cart__cartinfo = cart__cartinfo.replace('.-'+this.dataset.id+'.', '.');
						}

						formData.append('cartinfo', cart__cartinfo);

						cartUpdate(formData, e.target.pathname, e.target, this);
					}
				}
			});
		}
	}
}
delToCart(document);