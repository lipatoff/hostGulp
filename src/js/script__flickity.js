/*Карусель 1 слайд*/
var el = document.querySelector('.js-slider');
if (el) {
    var flkty1 = new Flickity(el);
}

/*Отзывы*/
var el = document.querySelector('.js-revs-slider');
if (el) {
    var flkty2 = new Flickity( el, {
        //Несколько слайдов
            groupCells: true,       //Листание по группам (false)   //true, 2, 3, '80%'
            //draggable: true,      //Свайпы (>1)
            //cellAlign: 'left',    //Выравнивание слайдов ('center')   //'left', 'right'
            //contain: true,        //Слайды прижимаются по краям (false)
            //imagesLoaded: true,     //Включать, если в карусели картинки!! (false)
        //Управление
            //friction: 0.1,            //Скорость листания
            //selectedAttraction: 0.11, //Дерганье (0)
            //prevNextButtons: false,       //Показывать стрелки (true)
            //pageDots: false,          //Показывать пагинацию (true)
        //Форма стрелок
            //arrowShape: 'M 0,50 L 60,00 L 50,30 L 80,30 L 80,70 L 50,70 L 60,100 Z',
            //arrowShape: { x0:10, x1:60, y1:50, x2:70, y2:40, x3:30 },
        //Автоплей
            autoPlay: true,                 //Автоплей (false)  //true, 1500
            pauseAutoPlayOnHover: false,    //Остановка автоплея при ховере (true)
        //Другое
            //fade: true,           //Нужно подключать файлы -fade. (false)
            //adaptiveHeight: true  //Автоизменение высоты (false)
    });
}


/*Карусель несколько слайдов*/
/*
var el = document.querySelector('.slider-other');
if (el) {
    var flkty2 = new Flickity( el, {
        //Несколько слайдов
            groupCells: true,       //Листание по группам (false)   //true, 2, 3, '80%'
            //draggable: true,      //Свайпы (>1)
            //cellAlign: 'left',    //Выравнивание слайдов ('center')   //'left', 'right'
            //contain: true,        //Слайды прижимаются по краям (false)
            imagesLoaded: true,     //Включать, если в карусели картинки!! (false)
        //Управление
            //friction: 0.1,            //Скорость листания
            //selectedAttraction: 0.11, //Дерганье (0)
            //prevNextButtons: false,       //Показывать стрелки (true)
            //pageDots: false,          //Показывать пагинацию (true)
        //Форма стрелок
            //arrowShape: 'M 0,50 L 60,00 L 50,30 L 80,30 L 80,70 L 50,70 L 60,100 Z',
            //arrowShape: { x0:10, x1:60, y1:50, x2:70, y2:40, x3:30 },
        //Автоплей
            autoPlay: true,                 //Автоплей (false)  //true, 1500
            pauseAutoPlayOnHover: false,    //Остановка автоплея при ховере (true)
        //Другое
            //fade: true,           //Нужно подключать файлы -fade. (false)
            //adaptiveHeight: true  //Автоизменение высоты (false)
    });
}
*/

/*  Вкл/выкл карусели
*   --------------------------------------
*   Принимает: el - элемент, width - ширина отключения, params - объект параметров для карусели
*/
/*
function flktyResizeShow(el, width, params){
    var isFlickity = false,
        flkty;

    window.addEventListener('resize', function(){
        if ( document.documentElement.clientWidth > width ) {
            if (isFlickity) { flkty.destroy(); isFlickity = !isFlickity; }
        }
        else if (!isFlickity) {
            flkty = new Flickity(el, params);
            isFlickity = !isFlickity;
        }
    });
}

var el = document.querySelector('.main-carousel3');
if (el) { flktyResizeShow(el, 967, {}); }
*/

/*Удалить
jQuery
LazyLoad
*/