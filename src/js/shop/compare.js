/*Сравнение*/

/*	Перемещение элементов
*	--------------------------------------
*	Принимает: compare - div
*/
function compareActionMovie(compare){

	var cCards = compare.querySelector('.c-cards'),
		cCard = cCards.querySelectorAll('.c-card'),
		cCard__drag = cCards.querySelectorAll('.c-card__drag'),

		jsC	= [],	//массив с индексами элементов
		c_i_drag = -1,
		c_i_active,

		position,
		cCard_width,
		cursor_margin,

		min,
		max,
		event_move,
		event_up,

		cProps = compare.querySelectorAll('.c-props__main:not(.c-props__main_same) .c-props__item'),
		cProp = [];

	for (var i = 0; cCard__drag[i]; i++) {
		cCard__drag[i].i = i;
		jsC[i] = i;
	}

	for (var j = 0; cProps[j]; j++) {
		cProp[j] = cProps[j].querySelectorAll('.c-prop');
	}

	function cCardsActive(i){
		if (cCard[jsC[c_i_active]]) cCard[jsC[c_i_active]].classList.remove('c-card_active');	//удаляем старый active
		
		if (c_i_drag == i) {
			if (c_i_active < i) { i++; }
			else { i--; }
		}

		c_i_active = i;
		if (cCard[jsC[c_i_active]]) cCard[jsC[c_i_active]].classList.add('c-card_active');
	}

	function cCardSearch(pageX){
		if (c_i_drag>-1){
			var next = 0,
				positionX = pageX - cursor_margin;

			if (positionX < min) {positionX = min;}
			else if (positionX > max) {positionX = max;}

			cCard__drag[jsC[c_i_drag]].style.left = positionX + 'px';
			
			if (position + cCard_width < positionX) { next = 1; } //Выбираем следующий
			else if (position - cCard_width > positionX){ next = -1; } //Выбираем предыдущий
			if (next!=0) {
				cCardsActive(c_i_active+next);

				position = cCard[jsC[c_i_drag]].getBoundingClientRect().left;
			}
		}
	}

	var cCardMoveListener = function(e) {
		cCardSearch(e.pageX);
	};

	var cCardStop = function(e) {e.preventDefault();};

	function cCardMove(){
		if (c_i_drag+1 != c_i_active){
			cCards.insertBefore(cCard[jsC[c_i_drag]], cCard[jsC[c_i_active]]);
			
			for (var j = 0; cProps[j]; j++) {
				cProps[j].insertBefore(cProp[j][jsC[c_i_drag]], cProp[j][jsC[c_i_active]]);
			}
			
			var newpos = c_i_active > c_i_drag ? c_i_active-1 : c_i_active;
			jsC.splice(newpos, 0, jsC.splice(c_i_drag, 1)[0]);
		}
	}

	var cCardUpListener = function(e) {
		cCardSearch(e.pageX);

		//drop container
			cCard[jsC[c_i_drag]].classList.remove('c-card_drag');
			if (cCard[jsC[c_i_active]]) cCard[jsC[c_i_active]].classList.remove('c-card_active');
			compare.classList.remove('compare_drag');

		//drop drag
			cCard__drag[jsC[c_i_drag]].classList.remove('c-card__drag_move');
			cCard__drag[jsC[c_i_drag]].style.left = 0;
			cCardMove();

		c_i_drag = -1;
		cCards.style.height = '';
		document.removeEventListener('selectstart', cCardStop, false);
		document.removeEventListener(event_move, cCardMoveListener, false);
		document.removeEventListener(event_up, cCardUpListener, false);		
	};

	var cCardDownListener = function(e) {
		if (c_i_drag<0 && e.target.classList.contains('c-card__drag')){
			event_move = e.type=='touchstart' ? 'touchmove' : 'mousemove';
			event_up = e.type=='touchstart' ? 'touchend' : 'mouseup';			

			min = this.getBoundingClientRect().left;
			max = min + this.clientWidth - e.target.clientWidth;

			cCards.style.height = this.clientHeight+'px';
			compare.classList.add('compare_drag');

			//get drag
				c_i_drag = jsC.indexOf(e.target.i);
				cCard_width = cCard__drag[jsC[c_i_drag]].clientWidth/2;
				cCard__drag[jsC[c_i_drag]].classList.add('c-card__drag_move');

			//get container
				position = cCard[jsC[c_i_drag]].getBoundingClientRect().left;	//позиция эл-та
				cCard[jsC[c_i_drag]].classList.add('c-card_drag');
				cursor_margin = e.pageX - position;	//смещение курсора

			cCardsActive(c_i_drag+1);
			cCardSearch(e.pageX);

			document.addEventListener('selectstart', cCardStop, false);
			document.addEventListener(event_move, cCardMoveListener, false);
			document.addEventListener(event_up, cCardUpListener, false);
		}
	};

	cCards.addEventListener('mousedown', cCardDownListener, false);
	cCards.addEventListener('touchstart', cCardDownListener, false);
}


var compare = document.querySelector('.compare');
if (compare) compareActionMovie(compare);