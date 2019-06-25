//Прилипание меню
var nav_scroll=document.querySelector('.nav_scroll'); 

if (nav_scroll) {
	if (typeof(CSS) !== 'undefined') {
		if (CSS.supports('position', 'sticky') || CSS.supports('position', '-webkit-sticky')) {
			nav_scroll=false;
		}
	}
}

if (nav_scroll) {
	var nav_scroll__top;

	function nav_scroll__scroll(){
		if (pageYOffset<nav_scroll__top){ nav_scroll.classList.remove('nav_scroll_fixed'); }
		else { nav_scroll.classList.add('nav_scroll_fixed'); }
	}

	window.addEventListener('resize', function(){
		window.removeEventListener('scroll', nav_scroll__scroll);
		nav_scroll.classList.remove('nav_scroll_fixed');

		if (getComputedStyle(nav_scroll).position!='fixed') {
			nav_scroll__top = Math.floor(nav_scroll.getBoundingClientRect().top+pageYOffset);
			nav_scroll__scroll();
			if (nav_scroll__top>0) window.addEventListener('scroll', nav_scroll__scroll);
		}
	});
}