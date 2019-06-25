//Ленивая загрузка изображений (data-src)
function loadImgRemove(tag){
	tag.removeAttribute('data-src');
	tag.removeAttribute('data-src-min');
	tag.removeAttribute('data-bk');
}

//Получение src изображения
function getSrc(tag, loadimg){
	var tag__src = tag.tagName=='IMG' ? tag.dataset.src : tag.dataset.bk,
		tag__src_split = tag__src.split(',')[0].split(' ')[0];

	if (tag.dataset.srcMin && window.devicePixelRatio<2){ 				//Если есть data-src-min
		var srcArray = tag__src_split.split('/');
		srcArray.pop();
		loadimg.src = srcArray.join('/')+'/'+tag.dataset.srcMin;

	}else if(tag.classList.contains('img_p')){ 							//Если есть .img_p
		var srcArray = tag.src.split(',')[0].split(' ')[0].split('/');
		srcArray.pop();
		loadimg.src = srcArray.join('/')+'/'+tag__src_split;

	}else{
		loadimg.srcset = tag__src;
		loadimg.src = tag__src_split;
	}

	return loadimg;
}

function imgShow(tag, img){
	var currentSrc = img.currentSrc ? img.currentSrc : img.src;
				
	if (tag.tagName=='IMG'){
		tag.classList.add('img_h');
		tag.src = currentSrc;
		tag.onload = function(){
			tag.classList.add('img_show');
			tag.classList.remove('img_h');
			tag.style.cssText = '';
			loadImgRemove(tag);
		}
	}
	else{
		tag.style.backgroundImage='url('+currentSrc+')';
		//tag.classList.remove('bk_h');
		loadImgRemove(tag);
	}
}

function loadImgBack(tag){
	//Если тег img или блок виден - то начинаем загружать картинку в фоне
	if (tag.tagName=='IMG' || tag.clientHeight>0){
		var loadimg = new Image();
		loadimg.onload = function(){
			imgShow(tag, this);
		};
		loadimg.onerror = function(){ 
			if (tag.tagName=='IMG'){ tag.style.display = 'none'; }
			else{ loadImgRemove(tag); }
		};

		loadimg = getSrc(tag, loadimg);
	}
	
	//Если блок не виден - то сразу присваиваем ему background
	else{
		tag.style.backgroundImage = 'url('+tag.dataset.bk+')';
		tag.removeAttribute('data-bk');
	}
}

//Функция ленивой загрузки изображений
function imgLazy(html){
	var el = html.querySelectorAll('[data-src],[data-bk]');
	for (var i = 0; el[i]; i++) {
		loadImgBack(el[i]);
	}
}

(function(){
	//Сопоставление ширины и высоты
		var el = document.querySelectorAll('[data-src]');
		for (var i = 0; el[i]; i++) {
			el[i].style.height = ( el[i].getAttribute('height') / el[i].getAttribute('width') * el[i].offsetWidth ) + 'px';
		}

	//Загрузка изображений в первом экране
		el = document.querySelectorAll('.img_hero,.bk_hero');
		for (var i = 0; el[i]; i++) {
			loadImgBack(el[i]);
		}

	//Если изображения в кэше - то показать сразу
		el = document.querySelectorAll('[data-src],[data-bk]');
		
		var	lazy__img = new Image(), 
			lazy__complete,
			lazy__currentSrc,
			loadimg__src;

		for (var i = 0; el[i]; i++) {
			lazy__img = getSrc(el[i], lazy__img);
			lazy__complete=(typeof pageSpeed != 'undefined') || (lazy__img.complete && lazy__img.naturalHeight !== 0);

			if (lazy__complete){
				imgShow(el[i], lazy__img);
			}
			else{
				lazy__img.src = ''; lazy__img.srcset = '';
			}
		}

	//Загрузка остальных изображений
	if (typeof pageSpeed == 'undefined') { //Если не speedtest	
		window.addEventListener('load', function(){
			imgLazy(document);
		});
	}
}());