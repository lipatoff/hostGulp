//Фикс бага iOS11
if (/iPad|iPhone|iPod/.test(navigator.userAgent) && /OS 11/.test(navigator.userAgent)){
	var ios_body = document.querySelector('body'),
		ios_top = 0,
		el = document.querySelectorAll('.modal-check');
	
	for (var i = 0; el[i]; i++) {
		el[i].addEventListener('change', function(){
			if (this.checked) ios_top = pageYOffset;
			ios_body.classList.toggle('body_fixed');
			if (this.checked) ios_body.style.top=-ios_top+'px'
			else {ios_body.style.top=0; window.scrollTo(0,ios_top);}
		});
	}
}
