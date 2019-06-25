var gulp 		 = require('gulp'),
	gutil 		 = require('gulp-util'),
	gulpif 		 = require('gulp-if'),						//Для if-логики в gulp
//	fs			 = require('fs'), 							//Встроенная библиотека для проверки файлов на существование
	taskListing  = require('gulp-task-listing'),			//Вывод списка тасков
	//css и js
	sass		 = require('gulp-sass'),					//Sass -> css
	autoprefixer = require('gulp-autoprefixer'),			//Префиксы -webkit- -moz- ...
	concat		 = require('gulp-concat'),					//Объединяет файлы
	gcmq 		 = require('gulp-group-css-media-queries'),	//Переносит медиа-запросы в конец файла
	penthouse	 = require('penthouse'),					//Ищет критический css
//	uncss		 = require('gulp-uncss'),					//Удаляет неиспользуемый css
	csso 		 = require('gulp-csso'),					//Сжимает css
	cssmin 		 = require('gulp-cssmin'),
	uglify		 = require('gulp-uglify'),					//Сжимает js
	//fonts
	run 		 = require('gulp-run'),						//Командная строка
	cssfont64 	 = require('gulp-cssfont64'),				//Переводит шрифт в base64
	//Файлы
	file		 = require('gulp-file'),					//Создание файлов
	del 		 = require('del'),							//Удаляет
	changed 	 = require('gulp-changed'),					//Только измененные файлы
	ftp          = require('vinyl-ftp'),					//FTP
	zip 		 = require('gulp-vinyl-zip'),				//Zip-архивы
	replace 	 = require('gulp-replace'),					//Поиск и замена
	removehtml 	 = require('gulp-remove-html'),				//Удаляет код из html
	htmlmin 	 = require('gulp-html-minifier'),			//Сжимает html
	fileinclude  = require('gulp-file-include'),			//Вставляет файл в файл
//	rename 		 = require("gulp-rename"),					//Переименовывает
	//images
//	tinypng		 = require('gulp-tinypng'),					//Сжимает img
	favicon 	 = require('gulp-favicons'),				//Favicon
	//Обработка ошибок
//	print 		 = require('gulp-print'),					//Вывод на экран файлов в потоке
	notify 		 = require('gulp-notify'),					//Обработка ошибок
	combiner 	 = require('stream-combiner2').obj;			//Объединение .pipe


// SETTING ////////////////////////////////////////////////////////

gulp.task('default', ['watch']);			//Default
gulp.task('help', taskListing);				//Help

var conn = ftp.create({							//Конфигурация FTP
		host:     '',
		user:     '',
		password: '',
		parallel: 5,
		log: gutil.log
	}),
	domen = 'https://ДОМЕН.ru',					//Домен
	build = false,								//Билд на боевой (включает критичный css, css через js и т.д.)

	patch = '/l-host/public_html',				//Папка на сервере
	pagulp = '/l-host/gulp',					//Папка проекта на сервере

	hosthtml = '/templates',					//Папка html на сервере
	hostcss  = '/css',							//Папка css на сервере
	hostjs 	 = '/js',							//Папка js на сервере
	hostfont = '/fonts',						//Папка fonts на сервере
	hostimg  = '/images',						//Папка img на сервере

	libs = [									//Библиотеки, которые нужны в libs
		'base/youtube',
		'base/textmask',
		'base/photoswipe',
		//'base/owl-carousel',
		'base/flickity',
	];

// LIBS ////////////////////////////////////////////////////////////
var	libs_css   = libs.map(function(e) {return e + '/*.{sass,css}'}),
	libs_js    = libs.map(function(e) {return e + '/*.js'}),
	libs_style = libs.map(function(e) {return 'src/libs/'+ e + '/style__lib.sass'});

gulp.task('l-css', function(){	//Сборка библиотек css
	return combiner(
		gulp.src(libs_css.concat([
			'!**/style__lib.sass',	//Не включать стили для style.css
			'libs.sass'				//Для кастомизации libs.min.css
		]), {cwd: 'src/libs'}),
		sass(),
		concat('libs.min.css'),
		/*uncss({html: [
			domen, 
			domen+'/about/', 
			]}),*/
		autoprefixer(['last 2 versions']),
		gcmq(),
		csso(),
		cssmin({keepSpecialComments : 0}),
		gulp.dest('dist'+hostcss),
		conn.dest(patch+hostcss)
	).on('error', notify.onError());
});

gulp.task('l-js', function(){	//Сборка библиотек js
	return combiner(
		gulp.src(libs_js, {cwd: 'src/libs'}),
		concat('libs.min.js'),
		uglify(),
		gulp.dest('dist'+hostjs),
		conn.dest(patch+hostjs)
	).on('error', notify.onError());
});

gulp.task('libs', ['l-css','l-js','watch'], function(){	//Сборка библиотек js
	return combiner(
		gulp.src('src/libs/base/**/*.{jpg,png,svg,gif}'),
		gulp.dest('dist'+hostimg),
		conn.dest(patch+hostimg)
	).on('error', notify.onError());
});

// JS /////////////////////////////////////////////////////////////

		gulp.task('_form', function(){	 //Генерирует сжатый файл форм
			return combiner(
				gulp.src(['src/js/**/*.html']),
				changed('middle/js'),
				htmlmin({collapseWhitespace: true, removeComments: true}),
				gulp.dest('middle/js')
			).on('error', notify.onError());
		});

gulp.task('js', ['_form'], function(){		 //Build js
	return combiner(
		gulp.src([
			'src/js/script.js',
			'src/js/before.js'
		]),
		fileinclude({
			prefix: '@@',
			basepath: 'src'
		}),
		gulpif(build, 
			uglify({mangle: {reserved: [ //Имена переменных/функций, которые надо сохранить
				'imgLazy',
				'loadImgBack',
				'pSwp',
				'formAssent',
				'userAuth'
			]}}),
		),
		gulp.dest('dist'+hostjs),
		conn.dest(patch+hostjs)
	).on('error', notify.onError());
});

// HTML ///////////////////////////////////////////////////////////

gulp.task('html_base', function(){	 //Build html base template
	return combiner(
		gulp.src('src/templates/template13/*.htm'),
		fileinclude({
			prefix: '@@@',
			basepath: 'src/templates/template13'
		}),
		gulpif(build, removehtml({keyword: 'build'}), removehtml()),
		fileinclude({
			prefix: '@@',
			basepath: 'src'
		}),
		gulp.dest('dist'+hosthtml+'/template13'),
		conn.dest(patch+hosthtml+'/template13')
	).on('error', notify.onError());
});

gulp.task('html', function(){	 //Build html
	return combiner(
		gulp.src(['src/templates/**/*.htm', '!src/templates/template13/*.htm']),
		changed('dist'+hosthtml),
		gulp.dest('dist'+hosthtml),
		conn.dest(patch+hosthtml)
	).on('error', notify.onError());
});

// CSS ////////////////////////////////////////////////////////////

gulp.task('sass', function(){	//Sass -> css
	return combiner(
		gulp.src(['src/sass/fontello.css'].concat(libs_style,[
			'src/sass/style.sass',
		])),
		sass(),
		concat('style.css'),
		autoprefixer(['last 2 versions']),
		gcmq(),
		csso(),
		cssmin(),
		replace(/([{,;])[^{},:;]*:unset;?/g, '$1'),   //Удаляет св-ва с unset
		csso(),
		cssmin(),
		gulp.dest('middle/css'),
		gulp.dest('dist'+hostcss),
		conn.dest(patch+hostcss)
	).on('error', notify.onError());
});

gulp.task('page404', function(){	//Sass -> css
	return combiner(
		gulp.src('src/sass/404.sass'),
		sass(),
		concat('404.css'),
		autoprefixer(['last 2 versions']),
		gcmq(),
		csso(),
		cssmin(),
		gulp.dest('dist'+hostcss),
		conn.dest(patch+hostcss)
	).on('error', notify.onError());
});

// CRITICAL CSS ///////////////////////////////////////////////////

				function penthouse_pref(filecrit, urll){		//Критический css без префиксов
					if (urll === undefined) {urll='';}
					var siteurl = domen+urll,
						csspatch = 'middle/css/style.css',
						viewidth = 1300,
						vieheight = 800;

					return penthouse({
							url: siteurl,
							css: csspatch,
							blockJSRequests: false,
							width: viewidth, height: vieheight
							},
							function (err, criticalCss) {
								return file(filecrit, criticalCss, { src: true })
									.pipe(csso())
									.pipe(gulp.dest('dist'+hostcss+'/critical'))
									.pipe(conn.dest(patch+hostcss+'/critical'));
							}
						);
				}

			gulp.task('_penthouse', function () { //Критический css
				return  penthouse_pref('home.css'),
						penthouse_pref('all.css', '/about/');
			});

		gulp.task('_crit', function () { //Критический css
			return run('gulp _penthouse').exec();
		});

gulp.task('crit', function () { //Критический css
	if (build){	gulp.start('_crit'); }
	else { console.log('Для критического css вкл. константу build'); }
});

// FONT ///////////////////////////////////////////////////////////

			gulp.task('_fonts64', ['fontello'],function () {				//Сборка шрифтов в base64. Имя файла шрифта по маске: <Font Name>--fw<Width>.woff / 'Open Sans'--fw300.woff
				var woff = combiner(
					gulp.src(['src/fonts/*.woff']),
					cssfont64(),
					concat('fonts_woff.css'),
					replace('--fw', ';font-weight:'),
					replace('--italic', ';font-style:italic'),
					csso(),
					gulp.dest('src/fonts/')
				).on('error', notify.onError());

				var woff2 = combiner(
					gulp.src(['src/fonts/*.woff2']),
					cssfont64(),
					concat('fonts_woff2.css'),
					replace('--fw', ';font-weight:'),
					replace('--italic', ';font-style:italic'),
					csso(),
					gulp.dest('src/fonts/')
				).on('error', notify.onError());

				return woff, woff2;
			});

		gulp.task('_fontssave', ['_fonts64'], function () { //Сохраненние шрифтов base64 на сервере
			return combiner(
				gulp.src(['src/fonts/fonts_woff.css','src/fonts/fonts_woff2.css']),
				conn.dest(patch+hostfont)
			).on('error', notify.onError());
		});

gulp.task('font', ['_fontssave', 'watch']); //Build fonts

// FONTELLO ///////////////////////////////////////////////////////

			gulp.task('_fonload', function() {					//Скачивание Fontello
				return run('fontello-cli install --config ./src/fonts/config.json --css ./src/fonts/fontello --font ./src/fonts/fontello').exec();
			});

		gulp.task('_fonmove', ['_fonload'], function() {	//Перемещение Fontello
			var woff = gulp.src('src/fonts/fontello/*.{woff,woff2}')
				.pipe(gulp.dest('src/fonts'));

			var css = gulp.src('src/fonts/fontello/fontello.css')
				.pipe(replace(/^@font-face(.*|\n*)*?\}/g, ''))
				.pipe(gulp.dest('src/sass'));

			return woff, css;
		});

gulp.task('fontello', ['_fonmove'], function () {		//Fontello
	return del('src/fonts/fontello');
});

// ICON ///////////////////////////////////////////////////////////

		gulp.task('_favcreate', function () {
			return gulp.src('src/icon.png')
			.pipe(favicon({
				background: '#fff',
				icons: {android: false, appleStartup: false, coast: false, firefox: false, windows: false, yandex: true}
				}))
			.on('error', gutil.log)
			.pipe(gulp.dest('middle/images/icons'));
		});

gulp.task('favicon', ['_favcreate'], function () {	//Favicon
		return gulp.src([
			'middle/images/icons/apple-touch-icon-76x76.png',
			'middle/images/icons/apple-touch-icon-120x120.png',
			'middle/images/icons/apple-touch-icon-152x152.png',
			'middle/images/icons/apple-touch-icon-180x180.png',
			'middle/images/icons/yandex-browser-50x50.png',
			'middle/images/icons/favicon-16x16.png',
		])
		.pipe(gulp.dest('dist'+hostimg+'/icons'))
		.pipe(conn.dest(patch+hostimg+'/icons'));
});

// BASESITE ///////////////////////////////////////////////////////

gulp.task('basesite', function () {			//Перенос других файлов, относительно корня сайта
	return combiner(
		gulp.src('src/basesite/**/*'),
		changed('dist'),
		gulp.dest('dist'),
		conn.dest(patch)
	).on('error', notify.onError());
});

// WATCH //////////////////////////////////////////////////////////

gulp.task('watch', function(){	//Watch
	gulp.watch([
				'src/sass/**/*.sass',
				'!src/sass/404.sass',
				'src/libs/**/style__*.sass'
				],
				['sass']);
	
	gulp.watch([
				'src/libs/**/*.{sass,css}', 
				'!src/libs/**/style__*.sass'
				], 
				['l-css']);

	gulp.watch([
				'src/templates/**/*',
				'!src/templates/template13/**/*'
				],
				['html']);
	gulp.watch('src/templates/template13/**/*', ['html_base']);

	gulp.watch('src/sass/404.sass', ['page404']);
	gulp.watch('src/libs/**/*.js', ['l-js']);
	gulp.watch('src/js/**/*', ['js'])
	gulp.watch('src/basesite/**/*', ['basesite']);
});

// FTP ////////////////////////////////////////////////////////////

		gulp.task('_save', ['crit'], function () {		// Сохраняет проект на FTP
			return combiner(
				gulp.src(['src/**/*', '.bowerrc', 'package.json', 'gulpfile.js'], { base: './' }),
				zip.dest('zip/project.zip'),
				conn.dest(pagulp)
			).on('error', notify.onError());
		});		

gulp.task('save', ['_save'], function () {		// Сохраняет проект на FTP
	gulp.start('watch');
});

				gulp.task('_savepreload', function () {
					return combiner(
						gulp.src(['src/**/*', '.bowerrc', 'package.json', 'gulpfile.js'], { base: './' }),
						zip.dest('zip/project_backup.zip')
					).on('error', notify.onError());
				});

			gulp.task('_preload', ['_savepreload'], function () {
				return combiner(
					conn.src(pagulp+'/project.zip'),
					gulp.dest('zip')
				).on('error', notify.onError());
			});

		gulp.task('_allclear', ['_preload'], function () {
			return del(['src', 'dist', 'middle']);
		});

gulp.task('load', ['_allclear'], function () {	// Загружает проект с FTP
	return combiner(
		zip.src('zip/project.zip'),
		gulp.dest('.')
	).on('error', notify.onError());
});