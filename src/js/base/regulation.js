//152ФЗ
var regulation = document.querySelector('.re');

if (regulation){
	if (!localStorage.getItem('regulation')){ 
		regulation.classList.remove('hide');
		document.querySelector('.re__button').onclick = function(){
			regulation.classList.add('hide');
			localStorage.setItem('regulation', 1); 
		}
	}
}