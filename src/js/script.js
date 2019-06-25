(function(){

//include('js/script__test.js');				//Тест функционала

window.addEventListener('load', function(){
	document.querySelector('body').classList.add('body_load');
	
	//Форма
		var phone_placeholder = false,				//Добавлять ли placeholder +7 (___) ___-__-__
			accent_after_submit = false;			//Добавлять согласие после кнопки Отправить
		@@include('js/base/form.js');				//Форма
		@@include('js/base/form__need-input.js');	//Отображение полей, в зависимости от надобности

		var iaddress_locations = '';				//'[{ "region": "Москва" }, { "region": "Московская" }]';
		@@include('js/base/iaddress.js');			//Поле адреса
		@@include('js/base/irange.js');				//Поле от-до

	//Меню
		@@include('js/base/nav_scroll.js');		//Прилипание меню
		@@include('js/base/nav_show.js');		//Прятанье меню
		@@include('js/base/sshow.js');			//Появление элементов при скролле и возрастание цифр

	//ИМ
		var shop_link = '/catalog/';		//Ссылка на корень ИМ
		@@include('js/shop/base.js');		//ИМ
		@@include('js/shop/filter.js');		//Фильтр
		@@include('js/shop/cart.js');		//Корзина
		@@include('js/shop/order.js');		//Оформление заказа
		
		@@include('js/shop/little.js');		//Малые
		@@include('js/shop/shopadd.js');	//Кнопка добавления (в сравнение, избранное и т.д.)
		@@include('js/shop/compare.js');	//Сравнение
		@@include('js/shop/favorite.js');	//Избранное

	//Ajax
		var ajax_lazy = true;					//Включает ленивый Ajax
		@@include('js/ajax__functions.js');		//Список функций, которые надо делать с контентом из Ajax
		@@include('js/base/ajax.js');			//Ajax

	//Анимации
		//include('js/base/yac.js');			//Анимация перехода к якорю
		//include('js/base/body_animation.js');	//Анимация переходов между страниами

	//Включить, если используется scroll
		var event = document.createEvent('Event');
		event.initEvent('resize', true, true);
		if (window.dispatchEvent(event)){}
		event.initEvent('scroll', true, true);
		if (window.dispatchEvent(event)){}

	//Другое
		@@include('js/script__ymaps.js');			//Яндекс.Карта
		//include('js/script__more-scripts.js');	//Подключение внешних скриптов
		//include('js/base/call_fixed.js');			//Убираем косяк с целевыми звонками (если они подключены)
});

@@include('js/base/ios_fixed.js');	//Фикс бага iOS11
//include('js/script__owl.js');		//Карусель
@@include('js/script__flickity.js');//Карусель
@@include('js/script__goals.js');	//Цели

}());