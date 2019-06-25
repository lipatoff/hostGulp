//PhotoSwipe
if (document.querySelector('a[data-size]')){
	var himg__photo = document.createElement('div'),
		imgs = [], uid = [],
		//blurstyle=document.getElementById('blurswipe'),
		touch = true, minforce = 0.17, himg_last = 0;

	uid[0] = new Array(); uid[1] = new Array();

	himg__photo.innerHTML='<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true"><div class="pswp__bg"></div><div class="pswp__scroll-wrap"><div class="pswp__container"><div class="pswp__item"></div><div class="pswp__item"></div><div class="pswp__item"></div></div><div class="pswp__ui pswp__ui--hidden"><div class="pswp__top-bar"><div class="pswp__counter"></div><button class="pswp__button pswp__button--close" title="Закрыть (Esc)"></button><button class="pswp__button pswp__button--share" title="Share"></button><button class="pswp__button pswp__button--fs" title="Во весь экран"></button><button class="pswp__button pswp__button--zoom" title="Масштаб"></button><div class="pswp__preloader"><div class="pswp__preloader__icn"><div class="pswp__preloader__cut"><div class="pswp__preloader__donut"></div></div></div></div></div><div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap"><div class="pswp__share-tooltip"></div></div><button class="pswp__button pswp__button--arrow--left"></button><button class="pswp__button pswp__button--arrow--right"></button><div class="pswp__caption"><div class="pswp__caption__center"></div></div></div></div></div>';
	document.body.appendChild(himg__photo);

	var openPhotoSwipe = function(indexx) {
		var pswpElement = document.querySelector('.pswp'),
			ind = uid[1][indexx],
			items = imgs[uid[0][indexx]];

		var options = {
			index: ind,
			showHideOpacity: true,
			history: false,
			getThumbBoundsFn: function(ind) {
				var thumbnail = items[ind].el,
					pageYScroll = window.pageYOffset || document.documentElement.scrollTop,
					rect = thumbnail.getBoundingClientRect();
				return {x:rect.left, y:rect.top + pageYScroll, w:rect.width};
			},    
		};
		var gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
		gallery.init();
	};

	function himg_cl(i) {
		return function(){ openPhotoSwipe(i); return false; }
	}

	function touchend(el){
		el.style.webkitTransform = 'scale(1)';
		el.classList.remove('touch');
		//blurstyle.classList.remove('show');
	}

	/*function touchopen(){
		blurstyle.classList.remove('show');
		blurstyle.classList.remove('time');
	}*/

	function onTouchEnd(e) {
		touch = true;
		touchend(this);
	}

	function onTouchForceChange(e) {
		forceValue = e.changedTouches[0].force-minforce;
		
		if (touch && (forceValue>0)) {
			e.preventDefault();
			this.classList.add('touch');
			//blurstyle.classList.add('show');
			//blurstyle.style.webkitBackdropFilter = 'blur('+(forceValue * 10)+'px)';
			//blurstyle.style.backgroundColor = 'rgba(0, 0, 0, '+(forceValue/5)+')';
			this.style.webkitTransform = 'scale(' + (1 + forceValue/20) + ')';
			if (forceValue>0.53){
				touch = false;
				//blurstyle.classList.add('time');
				//setTimeout(touchopen, 500);
				this.click();
				touchend(this);
			}
		} else {
			touchend(this);
		}
	}

	function pSwp(html){
		var himg = html.querySelectorAll('a[data-size]');
		
		if (himg.length>0){

			for (var i = 0; himg[i]; i++) {
				himg[i].onclick = himg_cl(himg_last);

				var img=himg[i].querySelector('img'),
					size=himg[i].dataset.size.split('x');

				var item = {
					src: himg[i].href,
					w: parseInt(size[0], 10),
					h: parseInt(size[1], 10),
					el: himg[i]
				};

				if (img){
					item.msrc = img.dataset.src;
					item.title = img.alt;
					el: img;
				}

				uid[0][himg_last] = (himg[i].dataset.pswpUid) ? himg[i].dataset.pswpUid : 0;

				if (!imgs[uid[0][himg_last]]) {imgs[uid[0][himg_last]] = new Array();}

				var r = imgs[uid[0][himg_last]].push(item);
				uid[1][himg_last] = r-1;
				himg_last++;

				himg[i].addEventListener('touchend', onTouchEnd, false);
				himg[i].addEventListener('touchforcechange', onTouchForceChange, false);			
			}
		}
	}

	pSwp(document);
}