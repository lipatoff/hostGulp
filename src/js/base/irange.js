/*Поле от-до*/

/*	Создание компонента
*	--------------------------------------
*	Принимает: irange - div с 2мя input
*/
function irangeCreate(irange){
	irangeInputs(irange);			//Добавление input-ов
	irangeRange(irange);			//Добавление ползунка
}

/*	Добавляем ползунок
*	--------------------------------------
*	Принимает: irange - div
*/
function irangeRange(irange){
	var range = document.createElement('div');
	range.classList.add('irange__range');
	range.innerHTML = '<div class="irange__fill"><div class="irange__fill-in"></div></div>';
	irange.appendChild(range);

	irange.range = range;
	irange.container = range.querySelector('.irange__fill');
	irange.fill = range.querySelector('.irange__fill-in');

	irangeActionMovie(irange);

	irangeRangePosition(irange.input_from);		//Устанавливаем позиции ползунка
	irangeRangePosition(irange.input_to);		//Устанавливаем позиции ползунка	
}


/*	Устанавливаем позицию ползунка
*	--------------------------------------
*	Принимает: input - поле ввода
*/
function irangeRangePosition(input){
	var irange = input.irange,
		position = (input.value - irange.min) * 100 / (irange.max - irange.min),
		to = input.to ? true : false;

	irangeButtonPosition(irange.fill, to, position);
}


/*	Устанавливаем позиции
*	--------------------------------------
*	Принимает: fill - фон ползунка, to - если true=правая позиция/false=левая, position - позицию
*/
function irangeButtonPosition(fill,to,position){
	if (to){
		fill.right = position;
		fill.style.right = 100 - parseFloat(position) + '%';
	}else{
		fill.left = position;
		fill.style.left = position + '%';
	}
}


/*	Перемещение ползунка
*	--------------------------------------
*	Принимает: irange - div
*/
function irangeActionMovie(irange){
	var range_active = false,
		position,
		min,
		max,
		event_move,
		event_up;

	function search(pageX){
		if (range_active){
			var to = false,
				irange = range_active,
				input,
				val_min = parseFloat(irange.min),
				val_max = parseFloat(irange.max),
				fill = irange.fill;

			position = (pageX - min) * 100 / max;

			if (position<0) position = 0
			else if (position>100) position = 100;

			if (fill.left + fill.right > position * 2){
				input = irange.input_from;
			}else{
				to = true;
				input = irange.input_to;
			}
			
			irangeButtonPosition(fill, to, position);

			//Обновляем input
				input.value = Math.floor(val_min + position * (val_max - val_min) / 100);
		}
	}

	var moveListener = function(e) {
		search(e.pageX);
	};

	var stop = function(e) {e.preventDefault();};

	var upListener = function(e) {
		search(e.pageX);
		irangeInputUpdateValue(range_active.input_from);
		irangeInputUpdateValue(range_active.input_to);
		range_active = false;
		document.removeEventListener('selectstart', stop, false);
		document.removeEventListener(event_move, moveListener, false);
		document.removeEventListener(event_up, upListener, false);		
	};

	var downListener = function(e) {
		if (!range_active && e.target == this.range){
			event_move = e.type=='touchstart' ? 'touchmove' : 'mousemove';
			event_up = e.type=='touchstart' ? 'touchend' : 'mouseup';			

			range_active = this;

			min = this.container.getBoundingClientRect().left;
			max = this.container.clientWidth;

			search(e.pageX);

			document.addEventListener('selectstart', stop, false);
			document.addEventListener(event_move, moveListener, false);
			document.addEventListener(event_up, upListener, false);
		}
	};

	irange.addEventListener('mousedown', downListener, false);
	irange.addEventListener('touchstart', downListener, false);
}


/*	Добавление input-ов
*	--------------------------------------
*	Принимает: irange - div
*/
function irangeInputs(irange){
	//Определяем роли каждого input'а и связываем их
	var el = irange.querySelectorAll('.irange__input');
	
	irange.min = el[0].min;
	irange.max = el[1].max;
	el[1].to = true;

	irange.input_from = el[0];
	irange.input_to = el[1];

	for (var i = 0; el[i]; i++) {
		el[i].irange = irange;
		el[i].setAttribute('autocomplete','off');	//отключаем автоподстановку html5
		irangeActionBlur(el[i]);
	}

	irange.addEventListener('input', function(e) {
		e.target.value = e.target.value.replace(/\D+/g,"");
	});
}


/*	Отслеживаем изменение input-ов
*	--------------------------------------
*	Принимает: input - поле ввода
*/
function irangeActionBlur(input){
	input.addEventListener('blur', function(){
		irangeInputUpdateValue(this);
		irangeRangePosition(this);
	});
}


/*	Обновляем значение input
*	--------------------------------------
*	Принимает: input - поле ввода
*/
function irangeInputUpdateValue(input){
	if (+input.value<+input.min || isNaN(parseInt(input.value))) input.value=input.min
	else if (+input.value>+input.max) input.value=input.max;

	//Обновляем min и max у соседнего input		
	if (input.to) input.irange.input_from.max = input.value
	else input.irange.input_to.min = input.value;
}


var el = document.querySelectorAll('.irange');
for (var i = 0; el[i]; i++) {
	irangeCreate(el[i]);
}