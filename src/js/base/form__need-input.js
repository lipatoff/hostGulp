/*Отображение полей, в зависимости от надобности*/
if (forms.length>0){
	var js_needInput = [],
		needInput__mas = [],
		needInput__form_input = [],
		el;

	//Отображаем / скрываем input
	function needInputShow(form_id, input_name){
		var input = js_needInput[form_id][input_name];
		if (input.show>0 == input.classList.contains('hide'))
		{
			input.classList.toggle('hide');
			
			for (var i = 0; needInput__form_input[form_id][input_name][i]; i++) {
				needInput__form_input[form_id][input_name][i].disabled = !(input.show>0);
			}
		}
	}


	//Добавляем форму в проверку
	function needInputFormAdd(form){
		el = form.querySelectorAll('[data-input]');

		//Если есть включаемые поля
		if (el.length>0)
		{
			var form_id = form.id;

			needInput__mas[form_id] = [];
			js_needInput[form_id] = [];
			needInput__form_input[form_id] = [];

			//Перебираем каждое включаемое поле
			for (var i = 0; el[i]; i++) {
				var input_name = el[i].dataset.input,
					needInput__need = form.querySelectorAll('[data-need-input='+input_name+']');

				js_needInput[form_id][input_name] = el[i];
				
				//Если show > 0 - то нужно отображать поле
				js_needInput[form_id][input_name].show = 0;

				//Элементы формы включаемого поля
				needInput__form_input[form_id][input_name] = el[i].querySelectorAll('.form__input');

				//Если есть need'ы
				if (needInput__need.length>0)
				{
					//Формируем массив needInput__mas[id формы][name need'а][id input'а]
					for (var k = 0; needInput__need[k]; k++) {
						var need_name = needInput__need[k].name;

						if (!needInput__mas[form_id][need_name]){
							needInput__mas[form_id][need_name] = [];
						}

						if (!needInput__mas[form_id][need_name][input_name]){
							needInput__mas[form_id][need_name][input_name] = needInput__need[k].checked;

							//Если need checked - то отображаем input
							if (needInput__need[k].checked) js_needInput[form_id][input_name].show++;
						}
					}

					needInputShow(form_id, input_name);
				}
			}

			//Отслеживаем переключение need'ов
			form.addEventListener('change', function(e){
				var form_id = this.id,
					need_name = e.target.name;
				if (needInput__mas[form_id][need_name]){
					var mas = needInput__mas[form_id][need_name],
						this_input_name = e.target.dataset.needInput;

					Object.keys(mas).forEach(function(input_name, input, arr) {
						if (mas[input_name] != (this_input_name == input_name)){
							needInput__mas[form_id][need_name][input_name] = !mas[input_name];
							if (mas[input_name]) js_needInput[form_id][input_name].show++
							else js_needInput[form_id][input_name].show--;
							needInputShow(form_id, input_name);
						}
					});
				}
			});

		}
	}


	for (var i = 0; forms[i]; i++) {
		needInputFormAdd(forms[i]);
	}
}