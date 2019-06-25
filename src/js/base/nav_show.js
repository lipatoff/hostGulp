//Прятанье меню
var nav_show=document.querySelector('.nav_show');

if (nav_show) {
	var nav_show__active = false,
		nav_show__go=0,
		nav_show__burger=document.querySelector('.burger'),
		nav_show__scrollPosition, 	//Позиция скролла
		nav_show__h, 		//Высота header+nav
		nav_show__body_h, 	//Высота body
		nav_show__window_h; //Высота экрана


	function nav_show__scroll(){
		if (nav_show__go==7){
			if (pageYOffset<nav_show__h || ((nav_show__scrollPosition>pageYOffset) && (pageYOffset+nav_show__window_h<nav_show__body_h))){
				nav_show.classList.remove('nav_show_hide');
			}else{
				nav_show.classList.add('nav_show_hide');	
			}			
			nav_show__scrollPosition=pageYOffset;
			nav_show__go=0;
		}else{
			nav_show__go+=1;
		}
	}

	window.addEventListener('resize', function(){
		if (nav_show__active!=(getComputedStyle(nav_show__burger).display=='none')){
			nav_show__active=!nav_show__active;
			if (nav_show__active){
				window.addEventListener('scroll', nav_show__scroll);
			}else{
				window.removeEventListener('scroll', nav_show__scroll);
				nav_show.classList.remove('nav_show_hide');	
			}
		}

		if (nav_show__active){
			nav_show__h=document.querySelector('.header').offsetHeight+nav_show.offsetHeight; //Высота header+nav
			nav_show__body_h=document.querySelector('body').offsetHeight; //Высота body
			nav_show__window_h=window.screen.availHeight; //Высота экрана
			nav_show__scrollPosition=pageYOffset; //Позиция скролла
			nav_show__go=0;
		}
	});


}




