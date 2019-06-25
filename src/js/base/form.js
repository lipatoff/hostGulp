//Вставка форм
var form_containers = document.querySelectorAll('.form-container[data-form]');
if (form_containers.length>0){
	var forms = document.createElement('div');
	forms.innerHTML='@@include("../middle/js/form__form.html")';

	for (var i = 0; form_containers[i]; i++) {
		form = forms.querySelector('#'+form_containers[i].dataset.form);
		if (form){
			form_containers[i].classList.add('form-container_load');
			form_containers[i].appendChild(form);
		}
	}

	forms.innerHTML='';
}


//Наблюдатель за изменениями в input
var inputObserver = new MutationObserver(function(mutations) {
	inputValidate(mutations[0].target);
});


/*	Подключение формы
*	--------------------------------------
*	Принимает: form - форму
*/
var input_notempty_all_types = new Array('date','datetime','select-multiple','select-one','checkbox','radio','hidden');

function formAdd(form){
	formSettings(form); 	//Настройки формы
	formAddAssent(form); 	//Cогласие на обработку персональных данных
	formMessages(form);		//Сообщения об отправке формы
	formAddInputs(form); 	//Добавление полей
	formValidate(form);		//Валидация формы перед отправкой
	formSubmit(form);		//Отправка формы
}


/*	Настройки формы
*	--------------------------------------
*	Принимает: form - форму
*/
function formSettings(form){
	if (form.classList.contains('form_mail')) form.form_mail=true;
	form.setAttribute('novalidate', 'novalidate'); //Отключаем html валидацию
}


/*	Добавляем согласие на обработку персональных данных
*	--------------------------------------
*	Принимает: form - форму
*/
function formAddAssent(form){
	if (typeof formAssent !== 'undefined'){
		var form__itemSubmit = form.querySelector('.form__item-submit');
		if (form__itemSubmit) {
			if (accent_after_submit) { form__itemSubmit.innerHTML += formAssent; }
			else { form__itemSubmit.innerHTML = formAssent+form__itemSubmit.innerHTML; }

		}
	}
}


/*	Сообщения формы
*	--------------------------------------
*	Принимает: form - форму
*	Возвращает: 
*/
function formMessages(form){
	var form__message = form.querySelector('.form__message');

	//Успешная отправка
		if (!form__message) {
			form__message = document.createElement('div');
			form__message.classList.add('form__message');
			form__message.innerHTML='<p class="title">Спасибо!</p><p>Скоро мы свяжемся с Вами!</p>';
			form.appendChild(form__message);					
		}
		form__message.classList.add('form__message_success');

	//Ошибка отправки
		if (form.form_mail) {
			var form__message_error = document.createElement('div');
			form__message_error.classList.add('form__message');
			form__message_error.classList.add('form__message_error');
			form.appendChild(form__message_error);
		}
}


/*	Добавление input
*	--------------------------------------
*	Принимает: form - форму
*/
function formAddInputs(form){
	var el = form.querySelectorAll('.form__input');
	form.form__input = el;
	for (var i = 0; el[i]; i++) {
		inputSettings(el[i]);		//Основные настройки поля
		inputTypeSettings(el[i], true);	//Доп настройки по типам

		var input_notempty_type = input_notempty_all_types.indexOf(el[i].type) != -1;

		if (!input_notempty_type) {
			inputEventBlur(el[i]);		//Обработка события onblur
			inputSessionStorage(el[i]);	//Сохранение в session storage
		}

		if (input_notempty_type || el[i].value!='') { el[i].classList.add('form__input_notempty'); el[i].notempty = true; }
	}
}


/*	Валидация формы перед отправкой
*	--------------------------------------
*	Принимает: form - форму
*/
function formValidate(form){
	form.addEventListener('submit', function(e) {
		if (!this.classList.contains('form_noajax')){ e.preventDefault(); }	//Если форма не ajax

		//Первая валидация формы
			if (!this.novalidate) formFirstValidate(this);

		if (this.invalid) {
			var el_error,
				el = this.elements;

			for (var i = 0; el[i]; i++) {
				if (el[i].form__error_code) {
					el_error = el[i];
					break;
				}
			}
			
			if ($(el_error).offset().top < pageYOffset) $('html, body').animate({scrollTop: $(el_error).offset().top - 80}, 400);
			e.preventDefault();
			e.stopImmediatePropagation();
		}	
	});
}


/*	Настройка правил для input
*	--------------------------------------
*	Принимает: input - поле
*/
function inputSettings(input){
	input.input_error = false;				//Ошибка поля
	input.notempty = !(input.value=='') || input_notempty_all_types.indexOf(input.type) != -1;	//Если поле не пустое

	//Добавляем * к обязательным полям
	if (input.required && input.parentNode.querySelector('.form__label')){
		input.parentNode.querySelector('.form__label').innerHTML+='<span class="form__star">*</span>';
	}
}


/*	Событие input onblur
*	--------------------------------------
*	Принимает: input - поле
*/
function inputEventBlur(input){
	input.addEventListener('blur', function(){
		//Если поле не пустое - добавляем класс
		if ((this.value=='')==this.notempty) {
			this.classList.toggle('form__input_notempty');
			this.notempty=!this.notempty;
		}
	});	
}


/*	Сохранение значения input в session storage
*	--------------------------------------
*	Принимает: input - поле
*/
function inputSessionStorage(input){
	if (input.value=='' && input.type!='textarea'){
		//Сохраняем значение
		input.addEventListener('blur', function(){
			if (this.name!='' && this.value!='' && !(this.form__error_code>0)) {
				sessionStorage.setItem(this.name, this.value);
			}
		});
		//Получаем значение
		if (sessionStorage.getItem(input.name)) input.value = sessionStorage.getItem(input.name);
	}
}


/*	Первая валидация формы
*	--------------------------------------
*	Принимает: form - форму
*/
function formFirstValidate(form){
	form.novalidate = true;
	form.errors_count = 0;
	form.invalid = false;
	form.classList.add('form_valid');

	form.addEventListener('input', function(e){if (e.target.classList.contains('form__input')){
	 	inputValidate(e.target);
	}});
	form.addEventListener('change', function(e){if (e.target.classList.contains('form__input')){
	 	inputValidate(e.target);
	}});

	var el = form.form__input;
	for (var i = 0; el[i]; i++){
		inputValidateSettings(el[i]);	//Добавляем контейнеры с текстом ошибок
		inputValidate(el[i]);			//Валидация поля
	}
}


/*	Настройки поля при включении активной проверки валидации
*	--------------------------------------
*	Принимает: input - поле
*/
function inputValidateSettings(input){
	//Добавляем контейнеры с текстом ошибок
		input.form__error_code = 0;
		input.form__error = document.createElement('span');

		if (input.type!='checkbox'){
			input.form__error.classList.add('error');
			input.form__error.classList.add('form__error');
			input.form__error.classList.add('hide');
			input.parentNode.appendChild(input.form__error);
		}

	//Если input может менять свою необходимость в форме
		if (input.classList.contains('form__input_validate')){
			inputObserver.observe(input, {attributes: true, attributeFilter: ['disabled']});
		}
}


/*	Проверка input
*	--------------------------------------
*	Принимает: input - поле
*/
function inputValidate(input){
	var form = input.form,
		input_good = true,
		value = input.value.trim(),
		error_code;

	//required
		if (input.required && value=='') error_code=1;

	//Проверка маски
		if (input.mask && !(value.search(input.mask)+1))
		{
			if (input.typejs && input.typejs == 'date') error_code=12
			else error_code=3;
		}

	if (!error_code && value!='') {
		if (input.minLength && (value.length<input.minLength)) error_code=2 //minLength
		else error_code = inputTypeSettings(input, false);
	}

	//Проверка системной валидации
		if (!error_code && value!='' && input.validity && !input.validity.valid) error_code=3;	

	//Если есть ошибки и поле активно
		if (error_code && !input.disabled) input_good = false;

	if (input_good==input.input_error) {
		input.classList.toggle('form__input_error');
		input.form__error.classList.toggle('hide');
		input.input_error=!input.input_error;

		//Проверка для form
			if (input_good) form.errors_count-=1
			else form.errors_count+=1;

			if ((form.errors_count==0)==form.invalid) {
				form.classList.toggle('form_error');
				form.classList.toggle('form_valid');
				form.invalid=!form.invalid;
			}
	}

	if (input_good){
		input.form__error_code=0;	
	}
	else if (input.form__error_code!=error_code){
		input.form__error_code=error_code;
		inputErrorType(input);
	}
}


/*	Настройка правил для input по каждому типу
*	--------------------------------------
*	Принимает: input - поле, create - true=добавляем элемент/false=проверяем уже добавленный элемент
*	Возвращает: error_code - код ошибки
*/
function inputTypeSettings(input, create){
	var error_code,
		value = input.value.trim();

	//Фикс типа для браузеров, которые его не поддерживают
		if (create && input.type=='text' && input.getAttribute('type') == 'date') input.typejs = 'date';

	var type = input.typejs ? input.typejs : input.type;

	switch (type) {

		case 'text':
			if (create){

				//Маска
					if (!input.classList.contains('iaddress')){
						input.mask = input.classList.contains('form__input_name') ? /^[a-zA-Zа-яА-Я ]*$/ : /^[a-zA-Zа-яА-Я0-9,.№;\/\- ]*$/;
					}

			}else{

				//Если передан код ошибки из стороннего js
					if (input.error_code) error_code=input.error_code;

				//Фикс для тех, кто не поддерживает type="date"
					if (input.typeie && input.typeie == 'date'){

					}
			
			}
			break;

		case 'textarea':
			if (create){

				//Автовысота
					input.addEventListener('keydown', function() {
						var el = this;
						setTimeout(function () { el.style.height = ''; el.style.height = el.scrollHeight + 'px'; }, 0);
					});

			}else{

				if (value.search(/https?:\/\//)+1) error_code=4;

			}
			break;

		case 'email':
			if (!create){

				if (!(value.search(/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Zа-яА-Я\-0-9]+\.)+[a-zA-Zа-яА-Я]{2,}))$/)+1)) {
					error_code=3;
				}
			
			}
			break;

		case 'tel':
			if (create){

				//Маска
					if (phone_placeholder && !input.placeholder) input.placeholder='+7 (___) ___-__-__';

					input.addEventListener('input', function(){
						//if (this.value.replace(/\D+/g,"").length==11 && this.value[0]!='+') this.value=this.value.substr(1);
						var value_n = this.value.replace(/\D+/g,"");
						if (value_n.length==11) {this.value='+7'+value_n.substr(1);}
						else if (value_n.length==12) {this.value='+7'+value_n.substr(2);}
					});

					var maskedInputController = vanillaTextMask.maskInput({
						inputElement: input,
						mask: ['+','7',' ','(',/[1-9]/,/\d/,/\d/,')',' ',/\d/,/\d/,/\d/,'-',/\d/,/\d/,'-',/\d/,/\d/]
					});

					input.addEventListener('focus', function(){ 
						if (this.value==''){ 
							var k = this; 
							setTimeout(function () { 
								k.value='+'; 
								maskedInputController.textMaskInputElement.update(); 
							}, 100); 
						} 
					}); 

				//Сбрасываем значение, если ввдено неверно
					input.addEventListener('blur', function(){
						if (this.value.indexOf('_')+1) {
							this.value='';
							maskedInputController.textMaskInputElement.update();
						}
					});

			}else{

				if (value.indexOf('_')+1) error_code=2;

			}
			break;

		case 'number':
			if (create){

				//Контролируем max и min
					input.addEventListener('input', function(){
						inputNumber(this);
					});

					if (input.min){
						input.addEventListener('blur', function(){
							if (+this.value<+this.min) this.value=this.min;
						});
					}

					input.addEventListener('keypress', function(e){
						if (!(e.key.search(/^[0-9]*$/)+1) || (this.maxLength && (this.value.length==this.maxLength))) {
							e.preventDefault();
						}
					});

					inputNumber(input);
			}else{

				if (!(value.search(/^[0-9]*$/)+1)) {
					error_code=3;
				}else{
					value= +value; input.value=value;

					if (input.min && (value<input.min)) error_code=8
					else if (input.max && (value>input.max)) error_code=9;
				}

			}
			break;

		case 'date':
			if (create){

				if (input.type!=type) {
					//Маска для браузеров, которые не поддерживают
						input.mask = /^(0?[1-9]|[12][0-9]|3[01])\.(0?[1-9]|1[012])\.(19\d\d|20[12]\d)$/;
					//Преобразование в нужный формат
						if (input.value!='' && input.value.indexOf('-')) {
							input.value = input.value.replace(/(\d{4})\-(\d{2})\-(\d{2})/,'$3.$2.$1');
						}
				}

				if (!input.max) input.max='2070-12-31';

			}else{

				//Преобразование для браузеров, которые не поддерживают
					if (input.type!=type) {
						var d = new Date(value.replace(/(\d{2})\.(\d{2})\.(\d{4})/,'$3-$2-$1')),
							month = '' + (d.getMonth() + 1),
							day = '' + d.getDate(),
							year = d.getFullYear();

						if (month.length < 2) month = '0' + month;
						if (day.length < 2) day = '0' + day;

    					value = [year, month, day].join('-');						
					}

				if (input.min && (value<input.min)) error_code=8
				else if (input.max && (value>input.max)) error_code=9;				

			}
			break;

		case 'checkbox':
			if (!create){

				if (input.required && !input.checked) {error_code=5;}

			}
			break;

		case 'file':
			if (!create){

				if (input_value.search(/^.*\.(?:jpg|jpeg|png|gif|docx|doc|xlsx|xls|rtf|pdf|rar|zip)\s*$/ig)!=0) error_code=3
				else if (!inputFileGetSize(input)) error_code=7;

			}
			break;

		/* НЕ РАБОТАЕТ
		case 'password':
			if (create){

				if (form.input_password) form.input_password2=i; 
				else form.input_password=i;

			}else{

				if (form.input_password2){
					if (form.input_password2==j) {
						input_good=input_value==form.form__input[form.input_password].value;
						error_code=6;
					} else {
						var input2 = form.form__input[form.input_password2];
						if ((input_value==input2.value)==input2.input_error){
							var event = document.createEvent('Event');
							event.initEvent(event_type, true, true);
							if (input2.dispatchEvent(event)){}
						}				
					}
				}

			}
			break;
		*/

	}

	return error_code;
}


/*	Проверка type number
*	--------------------------------------
*	Принимает: input[type=number]
*/
function inputNumber(input){
	if (input.maxLength>0 && (input.value.length>input.maxLength)){
		input.value = input.value.slice(0, input.maxLength);
	}
	if (input.max){
		if (input.value.length>input.max.length) input.value=input.value.substr(0,input.max.length);
		if (+input.value>+input.max) input.value=input.max;
	}
}


/*	Получение размера файла
*	--------------------------------------
*	Принимает: file - поле файла
*/
function inputFileGetSize(file) {
    var file__obj;
    if ( typeof ActiveXObject == 'function' ) { file__obj = (new ActiveXObject('Scripting.FileSystemObject')).getFile(file.value); }
    else { file__obj = file.files[0]; }
    
    if(file__obj.size > 1024 * 1024){ return false; } // Размер
    return true;
}


/*	Вывод типа ошибки
*	--------------------------------------
*	Принимает: input - поле
*/
function inputErrorType(input) {
	var errors = {
		1: 'Пустое поле',
		2: 'Мало символов',
		3: 'Неверный формат',
		4: 'Содержит адрес сайта',
		5: 'Обязательное поле',
		6: 'Пароли не совпадают',
		7: 'Превышает макс.размер',
		8: 'Мин: '+input.min,
		9: 'Макс: '+input.max,
		10: 'Адрес не найден',
		11: 'Не полный адрес',
		12: 'Формат даты: dd.mm.yyyy'
		};

	input.form__error.innerText = errors[input.form__error_code];
}


/*	Отправка формы
*	--------------------------------------
*	Принимает: form - форму
*/
function formSubmit(form){
	if (!form.classList.contains('form_noajax')){ //Если форма ajax
		form.addEventListener('submit', function(){
			var form=this,
				elements=form.elements,
				page_h1 = document.querySelector('h1'),
				formData = new FormData(),
				request = new XMLHttpRequest(),
				link = form.form_mail ? '/form.php' : window.location;

			formData.append('js', '1');
			formData.append('submit_question', '1');
			formData.append('ourl', window.location.href.split('?')[0]); //Текущая страница

			if (page_h1) {formData.append('oh1', page_h1.innerText);} //Заголовок страницы

			for (var i = 0; elements[i]; i++) {
				var input = elements[i];
				if (!input.disabled && input.name!=''){

					switch (input.type) {
						case 'file':
							var file = input.files[0];
							if (file) formData.append(input.name, file);
							break;

						case 'submit':
							input.classList.add('button_send');
							break;

						default: 
							var element_val = input.value;
							if (input.classList.contains('form__input_name')){
								element_val=input.value.replace(/(^|\s)\S/g, function(l){ return l.toUpperCase() });
							}
							formData.append(input.name, element_val);
					}
				}
			}

			request.open('POST', link, true);

			request.onload = request.onerror = function() {
				if (request.status >= 200 && request.status < 400 && (!form.form_mail || request.responseText == 'success')) { 
					form.classList.add('form_success');
					goals_form(form.id);						
				}else{
					var errortext = request.responseText && form.form_mail ? request.responseText : 'Попробуйте позже';							
					form.querySelector('.form__message_error').innerHTML='<p class="form__title title form__title_error">Возникла ошибка!</p><p>'+errortext+'</p>';
					form.classList.add('form_no-success');
				}
			};

			request.send(formData);
		});
	}
}


/*Перебор всех форм*/
var forms = document.querySelectorAll('.form');
for (var i = 0; forms[i]; i++) {
	formAdd(forms[i]); //Настройка формы
}

var el = document.querySelectorAll('[type=number]');
for (var i = 0; el[i]; i++) {
	el[i].pattern = '\\d*';
}