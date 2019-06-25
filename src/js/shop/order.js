/*Оформление заказа*/
var delivery = document.querySelector('.delivery');
if (delivery){
	var payment__item = document.querySelectorAll('.payment__item'),
		delivery__items = delivery.querySelector('.delivery__items'),
		js_payments = []
		payment__input = [];

	for (var i = 0; payment__item[i]; i++) {
		js_payments[i] = payment__item[i].dataset.delivery.split(',');
		payment__input[i] = payment__item[i].querySelector('input');
	}

	delivery__items.addEventListener('change', function(e){
		var delivery_active = e.target.value,
			delivery_first = -1,		//id эл-та для выделения
			delivery_remove = false;	//нужно ли обновлять выделенный эл-нт

		for (var i = 0; js_payments[i]; i++) {
			if (js_payments[i].indexOf(delivery_active) != -1){
				payment__item[i].classList.remove('hide');
				if (delivery_first<0) delivery_first = i;
			}else{
				payment__item[i].classList.add('hide');
				if (payment__input[i].checked) delivery_remove = true;
			}
		}

		if (delivery_remove) {
			payment__input[delivery_first].checked = true;
		}
	});
}

