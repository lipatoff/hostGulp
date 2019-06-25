/*Ajax-загрузка*/
	if (ajax_more){
		var pHeight=document.documentElement.clientHeight, pBottom;

		window.addEventListener("resize", function(){
			if (ajax_go) {
				ajax_more_top=ajax_more.getBoundingClientRect().top+pageYOffset; //top эл-та
				pHeight=document.documentElement.clientHeight; //Низ окна
			}
		});

		window.addEventListener("scroll", function(){
			if (ajax_go) {
				pBottom = pageYOffset+pHeight*1.5;
				if (pBottom>ajax_more_top){
					ajax_go=false;
					ajax_more.classList.add('ajax-last');
					ajax_more.onclick();
				}
			}
		});
	}
/**/