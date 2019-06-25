//Список функций, которые надо делать с контентом из Ajax
function ajax_functions(new__html){

	imgLazy(new__html);	//Ленивая загрузка изображений
	//pSwp(new__html);	//Просмоторщик изображерий
	addToCart(new__html);	//Добавление в корзину
	shopAdd(new__html);		//Кнопка добавления (в сравнение, избранное и т.д.)

	return new__html;
}