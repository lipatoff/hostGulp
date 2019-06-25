/*Малые*/
var little = [],
	el = document.querySelectorAll('.little[data-name]');

for (var i = 0; el[i]; i++) {
	el[i].count = el[i].querySelector('.little__count');

	little[el[i].dataset.name] = el[i];
}

function littleVal(id, newcount, html){
	if ( id in little > -1 ){
		little[id].count.innerHTML = newcount > 999 ? '..999' : newcount;
		if (newcount>0 != little[id].classList.contains('little_active') ) little[id].classList.toggle('little_active');
	}
}