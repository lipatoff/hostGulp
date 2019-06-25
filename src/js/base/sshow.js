//Появление элементов при скролле и возрастание цифр
var sshow = document.querySelectorAll('.sshow, .sshows>*, [data-num]');

if (sshow.length>0){
	//Анимация возрастания цифр
	function sshow__plus(el,data,now){ //Эл-нт, конечное число, текущее число
		now++; 
		el.innerText=now;
		if (data!=now) setTimeout( function(){ sshow__plus(el,data,now) }, 50);
	};

	//Появление эл-ов
	function sshow__go(el){
		el.classList.add('sshow-show');
		//Если есть data-num
		if (el.dataset.num){
			sshow__plus(el,el.dataset.num,+el.innerText)
		}
	};	

	//Если поддерживается IntersectionObserver
	if ('IntersectionObserver' in window) {
		var observer__options = {
			rootMargin: '-100px',
			threshold: [0,1]
			};

		var observer = new IntersectionObserver(function(items) {
			items.forEach(function(el) {
				if(el.isIntersecting && el.intersectionRatio>0) {
					sshow__go(el.target);
					//Отключаем слежение
					observer.unobserve(el.target);
				}
			});
		}, observer__options);

		for (var i = 0; sshow[i]; i++) {
			observer.observe(sshow[i]);
		}
	}
	//Если не поддерживается IntersectionObserver
	else {
		for (var i = 0; sshow[i]; i++) {
			sshow__go(sshow[i]);
		}	
	}
}