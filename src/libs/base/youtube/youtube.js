//Загрузка видео с YouTube
var youtube=document.querySelectorAll('.youtube');

if (youtube) {
	var youtube__url,			// Адрес видео
		youtube__width, 		// Ширина проигрывателя
		youtube__quality, 		// Качество
		youtube__container,		// Контейнер для видео
		youtube__type=!window.matchMedia('(pointer: coarse)').matches;	// Тип загрузки (true=на сайте, false=в youtube) 

	window.addEventListener('load', function(){

		//Если загрузка видео на сайте
		if (youtube__type){	
			function youtube__click(url) {
				return function(){
					this.classList.add('youtube__container_open');
					this.innerHTML='<iframe class="youtube__video" src="https://www.youtube.com/embed/'+url+'?autoplay=1&iv_load_policy=3&modestbranding=1&rel=0" frameborder="0" allowfullscreen></iframe>';
					this.onclick=function(){};
				}
			}
		}

		for (var i = 0; youtube[i]; i++) {
			youtube__url=youtube[i].dataset.url;

			if (youtube__type) {
				youtube__container = document.createElement('div');
			}else{
				youtube__container = document.createElement('a');
				youtube__container.setAttribute('href','https://youtu.be/'+youtube__url);
				youtube__container.setAttribute('target','_blank');
			}
			
			youtube__container.classList.add('img_show');
			youtube__container.classList.add('youtube__container');

			//Передан data-title - Заголовок
			if (youtube[i].dataset.title){
				youtube__container.innerHTML='<p class="youtube__title">'+youtube[i].dataset.title+'</p>';
			}
			
			//Передана data-img - Фоновая картинка
			if (youtube[i].dataset.img){
				youtube__container.style.backgroundImage='url('+youtube[i].dataset.img+')';
			}else if (typeof pageSpeed == 'undefined'){ //Если не speedtest
				youtube__width=youtube[i].offsetWidth;
				if(youtube__width>640){youtube__quality='maxres';}
				else if(youtube__width>480){youtube__quality='sd';}
				else if(youtube__width>320){youtube__quality='hq';}
				else if(youtube__width>120){youtube__quality='mq';}
				else {youtube__quality='';}

				youtube__container.style.backgroundImage='url(https://i.ytimg.com/vi/'+youtube__url+'/'+youtube__quality+'default.jpg)';
			}
			
			youtube[i].appendChild(youtube__container);

			if (youtube__type) {
				youtube__container.onclick=youtube__click(youtube__url);
			}
		}
	});
}