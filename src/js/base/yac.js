//Анимация перехода к якорю
var yac = document.querySelectorAll('a[href^="#"]');
if (yac.length>0){
	function yac__scroll(i) { 
		return function(){
		    var target = yac[i].getAttribute('href');
			$('html, body').animate({scrollTop: $(target).offset().top+pageYOffset}, 400);
			return false;
		}
	}
	for (var i = 0; yac[i]; i++){
		yac[i].onclick = yac__scroll(i);
	}
}