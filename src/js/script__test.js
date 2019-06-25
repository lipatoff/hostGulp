//Консоль для mobile

var console_m = document.createElement('div');
console_m.classList.add('console_m');
console_m.style.cssText = 'background-color: #fff;position: fixed;z-index: 999;bottom: 0;height: 50vh;left: 0;right: 0;overflow:auto;box-shadow: 0 0 17px;';

document.querySelector("body").appendChild(console_m);

console_m.onclick = function(){console_m.innerHTML='';}

function alertObj(obj) { 
    var str = ""; 
    for(k in obj) { 
        str += k+": "+ obj[k]+"\r\n"; 
    } 
    alert(str); 
}

function consolelog(info){

	if (typeof info == 'object') {
		var str = ''; 
		for(k in info) { 
			if (info[k] && !(typeof info[k] == 'object') && !(typeof info[k] == 'function')){
		    	str += k+": "+ info[k]+'<br/>'; 
		    }
		} 
		info = str+"------------------------ <br/><br/>";
	}
	
	console_m.innerHTML=info+'<br/>'+console_m.innerHTML;
}

///////////////////////////////////


window.addEventListener('resize', function(){

});

window.addEventListener('scroll', function(){
	
});

