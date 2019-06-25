//Выделение меню и скролл к элементам
var yac__menu_li=[], 
	yac__menu_active=99,
	yac__menu_active_new=99,
	yac__body=document.querySelector('body'), 
	yac__body_h=yac__body.offsetHeight,
	yac__showmenu=document.querySelector('.nav__showmenu'),
	yac__scroll_action=true;

function yac__scroll_to(i) { 
	return function(){
		yac__showmenu.checked=false;
		yac__scroll_action=false;
		$('html, body').animate({
			scrollTop: 1+Math.floor(yac__menu_li[i][1].getBoundingClientRect().top+pageYOffset)},
			400, 
			function() {
				yac__scroll_action=true;
				window.dispatchEvent(new Event('scroll'));
			  }
		);
		return false;
	}
}

var el = document.querySelectorAll('a[href^="#"]');
for (var i = 0; el[i]; i++) {
	var el_href = el[i].getAttribute('href'),
		yac__id = document.querySelector(el_href),
		yac__block = document.querySelector(el_href+'+*');

	yac__menu_li[i] = [
		el[i], 		//a пункта меню
		yac__id,	//якорь, на который идет ссылка
		yac__block, //блок, на который идет ссылка
		0, 			//top эл-та
		0 			//bottom эл-та
	];

	el[i].onclick = yac__scroll_to(i);
}

function yac__refresh(){ 
	for (var i = 0; yac__menu_li[i]; i++) {
		yac__menu_li[i][3] = Math.floor(yac__menu_li[i][1].getBoundingClientRect().top+pageYOffset); //top эл-та
		yac__menu_li[i][4] = yac__menu_li[i][3]+yac__menu_li[i][2].offsetHeight; //bottom эл-та
	}
	yac__body_h=yac__body.offsetHeight;
}

window.addEventListener('resize', function(){
	yac__refresh();
});

window.addEventListener('scroll', function(){
	if (yac__scroll_action){
		if (yac__body_h!=yac__body.offsetHeight){yac__refresh();}

		if (yac__menu_active!=99 && (pageYOffset<yac__menu_li[yac__menu_active][3] || pageYOffset>=yac__menu_li[yac__menu_active][4])) {

			yac__menu_active_new=99;

			if (pageYOffset<yac__menu_li[yac__menu_active][3]) {
				for (var i = yac__menu_active-1; i>=0; i--) {
					if (pageYOffset>=yac__menu_li[i][3] && pageYOffset<yac__menu_li[i][4]) {
						yac__menu_active_new=i;
						break;
					}
				}
			}else{
				for (var i = yac__menu_active+1; yac__menu_li[i]; i++) {
					if (pageYOffset>=yac__menu_li[i][3] && pageYOffset<yac__menu_li[i][4]) {
						yac__menu_active_new=i;
						break;
					}
				}
			}
			setTimeout(function(){
				if (yac__menu_active!=99){
					yac__menu_li[yac__menu_active][0].classList.remove('menu__link_active');
				}
				yac__menu_active=yac__menu_active_new;

				if (yac__menu_active_new==99) { history.replaceState('','', '/'); } 
				else {
					yac__menu_li[yac__menu_active_new][0].classList.add('menu__link_active');
					history.replaceState("", "", yac__menu_li[yac__menu_active_new][0]);
				}
			}, 0);
			
		} else if (yac__menu_active==99) {
			for (var i = 0; yac__menu_li[i]; i++) {
				if (pageYOffset>=yac__menu_li[i][3] && pageYOffset<yac__menu_li[i][4]) { 
					yac__menu_li[i][0].classList.add('menu__link_active');
					history.replaceState("", "", yac__menu_li[i][0]);
					yac__menu_active=i;
					break;
				}
			}
		}
	}
});

if (location.hash){
	if (document.querySelector(location.hash)){
		yac__showmenu.checked=false;
		yac__scroll_action=false;

		$('html, body').animate({
			scrollTop: 1+Math.floor(document.querySelector(location.hash).getBoundingClientRect().top+pageYOffset)},
			400, 
			function() {
				yac__scroll_action=true;
				window.dispatchEvent(new Event('scroll'));
			  }
		);
	}else{
		history.replaceState('', '', '/');
	}
}