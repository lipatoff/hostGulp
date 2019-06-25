//Яндекс.Карта 
//Вызов <div class="yamap js-yamap" data-center="55.886978, 37.612245" data-add="[55, 37][53, 27]" data-zoom="15"></div>

/*  Основная функция добавления
*	--------------------------------------
*	Принимает: yamap - контейнер карты
*/
function yamapAdd(yamap){
	var myMap = yamapAddMap(yamap),	//получаем объект карты
		coordinates = yamapAddCoordinates(myMap, yamap.dataset.add); //получаем координаты

	for (var i = 0; coordinates[i]; i++) {
		myMap.geoObjects.add( yamapPlacemark(coordinates[i]) );	//добавляем координаты на карту
	}

	yamap.myMap = myMap;
}

/*  Добавление карты
*	--------------------------------------
*	Принимает: yamap - контейнер карты
*	Возвращает: myMap - объект карты
*/
function yamapAddMap(yamap){
	var ycentr = yamap.dataset.center ? yamap.dataset.center : '55.765260, 37.631829',
		ycenter = ycentr.split(','),
		yzoom = yamap.dataset.zoom ? Number(yamap.dataset.zoom) : 15;

	ycenter[1] = ycenter[1].trim();
	ycenter[0]=Number(ycenter[0]); ycenter[1]=Number(ycenter[1]);
	
	var myMap = new ymaps.Map(yamap,
		{
			center: [ycenter[0], ycenter[1]], //[55.784101, 37.712003],
			zoom: yzoom, //17,
			controls: [/*'zoomControl', 'typeSelector'*/]
		}, {
			searchControlProvider: 'yandex#search'
		});

	myMap.behaviors.disable('scrollZoom');

	return myMap;
}

/*  Получение списка координат
*	--------------------------------------
*	Принимает: 	myMap - карта
*				yadd - список доп координат (в формате '[x,y][x, y][x,y]...')
*	Возвращает: coordinates - массив координат
*/
function yamapAddCoordinates(myMap, yadd){
	var coordinates = [myMap.getCenter()];

	//Если несколько точек
	if (yadd){
		var ynext;

		yadd = yadd.split('[');

		for (var i = 1; yadd[i]; i++){
			ynext = yadd[i].substring(0, yadd[i].length - 1).split(',');
			coordinates.push([ynext[0],ynext[1].trim()]);
		}
	}

	return coordinates;
}

/*  Добавление координат
*	--------------------------------------
*	Принимает: 	coordinates - координата
*	Возвращает: placemark - объект координаты
*/
function yamapPlacemark(coordinates){
	var placemark = new ymaps.Placemark(coordinates, {
		//balloonContent: '<img src="http://site.ru/images/map/photo.jpg" alt="Фото здания">'	//Текст при клике на метку
		//iconCaption: 'Текст',
		}, {
		iconColor: "@@include('color-base.txt')",		//Цвет метки
		//preset: 'islands#dotIcon'					//Вид метки
		//iconLayout: 'default#image',
		//iconImageHref: 'http://site.ru/images/map.png', //Путь до изображения
		//iconImageSize: [28, 41], 					 	  //Размер изображения
		//iconImageOffset: [-14, -41]					  //Смещение изображения
		});

	/*Получение адреса*/
         ymaps.geocode(placemark.geometry.getCoordinates(), {results: 1}).then(function (res) {
             var newContent = res.geoObjects.get(0) ? res.geoObjects.get(0).properties.get('name') : '';
             placemark.properties.set('iconCaption', newContent);
         });

    return placemark;
}



if (typeof pageSpeed == 'undefined') { //Если не speedtest
	
	var yamaps = document.querySelectorAll('.js-yamap');
	if (yamaps.length>0) {
		var yscript = document.createElement('script');
		yscript.src = 'https://api-maps.yandex.ru/2.1/?lang=ru_RU';
		document.getElementsByTagName('head')[0].appendChild(yscript);

		yscript.onload = function(){
			ymaps.ready(function(){
				for (var i = 0; yamaps[i]; i++) {
					yamapAdd(yamaps[i]);
				}
			});
		}
	}

}