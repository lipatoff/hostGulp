//Анимация переходов между страниами
var body_animation=document.querySelector('.body_animation');

if (body_animation){
	body_animation.classList.add('body_visible');

	var body__a = document.querySelectorAll("a[href^='/']:not([href*='.'])");

	for (var i = 0; body__a[i]; i++) {
		body__a[i].addEventListener('click', function(event){
			var newpage = this.href;
			event.preventDefault();
			body_animation.classList.remove('body_visible');
			setTimeout(function(){document.location.href = newpage;}, 300);
		});
	}
}