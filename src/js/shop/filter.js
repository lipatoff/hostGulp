/*Фильтр*/
var filter = document.querySelector('.filter');
if (filter){
	filter.addEventListener('submit', function(e){
		var el=this.elements,
			producer_set,	//true - когда установлен хотя бы 1 флажок на производителе
			producer_empty;	//true - когда НЕ установлен хотя бы 1 флажок на производителе

		//Убираем значения по умолчанию
		for (var i = 0; el[i]; i++) {
			var delete_el = false;

			if (el[i].classList.contains('filter__select')){			//Выпадающее поле
				delete_el = el[i].value==0;
			}else if (el[i].classList.contains('filter__from')){		//Цена от
				delete_el = el[i].value==el[i].min;
			}else if (el[i].classList.contains('filter__to')){			//Цена до
				delete_el = el[i].value==el[i].max;
			}else if (el[i].classList.contains('filter__producer')){	//Производитель
				if (el[i].checked) producer_set = true
				else producer_empty = true;
			}

			if (delete_el) el[i].disabled=true;
		}

		//Убираем производителей, если выбраны все
			if (producer_set && !producer_empty)
			{
				for (var i = 0; el[i]; i++) {
					if (el[i].classList.contains('filter__producer')){
						el[i].disabled=true;
					}
				}		
			}
	});
				
}