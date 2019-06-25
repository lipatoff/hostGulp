//Карусель 1 слайд
    $('.js-slider').owlCarousel({
        items : 1,                          //Кол-во видимых элементов (5)  
        //addClassActive : false,         //Добавить класс .active видимым элементам (false)
        //itemsDesktop : [1199,4],        // -//- для данной ширины ([1199,4])
        //itemsDesktopSmall : [980,3],    // -//- для данной ширины ([980,3])
        //itemsTablet: [768,2],           // -//- для данной ширины ([768,2])
        //itemsTabletSmall: false,        // -//- для данной ширины (false)
        //itemsMobile : [479,1],          // -//- для данной ширины ([479,1])
        singleItem : true,                //Отображает только 1 элемент (false)
        autoPlay : false,                 //Авто-прокрутка (5000) 
        //itemsScaleUp : false,           //Запрещает растягивание (false)
        //scrollPerPage : false,          //false - переход по элементам, true - по страницам (false)  
        //pagination : false,             //Показать пагинацию (true)
        //paginationSpeed : 800,          //Скорость пагинации (800)
        navigation : true,                //Отображать кнопки (false)
        //navigationText : ['',''],       //Текст кнопок (['',''])
        //responsive: true,               //Проверка изменения ширины окна (true)
        //slideSpeed : 400,               //Скорость смены слайдов (400)
        //rewindSpeed : 600,              //Скорость перемотки (600)
        //stopOnHover : true,             //Остановить прокрутку при наведении мышки (true)
        //responsiveBaseWidth: '.container',  //Какой элемент роверять на изменение ширины ('.container')        
        transitionStyle : 'goDown',       //Добаляет CSS3 transition стили перехода (false) // fade, scaleUp, backSlide, goDown
    });

/*
//Карусель несколько слайдов
    $('.slider-other').owlCarousel({
        items : 4,                      //Кол-во видимых элементов (5)  
        //addClassActive : false,         //Добавить класс .active видимым элементам (false)
        //itemsDesktop : [1199,4],        // -//- для данной ширины ([1199,4])
        //itemsDesktopSmall : [980,3],    // -//- для данной ширины ([980,3])
        //itemsTablet: [768,2],           // -//- для данной ширины ([768,2])
        //itemsTabletSmall: false,        // -//- для данной ширины (false)
        //itemsMobile : [479,1],          // -//- для данной ширины ([479,1])
        singleItem : true,                //Отображает только 1 элемент (false)
        autoPlay : false,                 //Авто-прокрутка (5000) 
        //itemsScaleUp : false,           //Запрещает растягивание (false)
        //scrollPerPage : false,          //false - переход по элементам, true - по страницам (false)  
        //pagination : false,             //Показать пагинацию (true)
        //paginationSpeed : 800,          //Скорость пагинации (800)
        navigation : true,                //Отображать кнопки (false)
        //navigationText : ['',''],       //Текст кнопок (['',''])
        //responsive: true,               //Проверка изменения ширины окна (true)
        //slideSpeed : 400,               //Скорость смены слайдов (400)
        //rewindSpeed : 600,              //Скорость перемотки (600)
        //stopOnHover : true,             //Остановить прокрутку при наведении мышки (true)
        //responsiveBaseWidth: '.container',  //Какой элемент роверять на изменение ширины ('.container')        
        transitionStyle : 'goDown',       //Добаляет CSS3 transition стили перехода (false) // fade, scaleUp, backSlide, goDown
    });
*/