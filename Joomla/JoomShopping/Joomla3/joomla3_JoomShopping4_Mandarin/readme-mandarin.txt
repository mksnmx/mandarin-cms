!Конфигурация Mandarin PM
1. Закидываем архив в корень сайта /
2. %domain%/installMandarin.php
3. Удалить %domain%/install-mandarin.php
4. %domain$/administrator/ Components > Joomshoping > Options. Payments, кликнуть на MandarinBank
5. Вкладка Config в самом верху
	Merchant ID: ...
	Merchant Secret: ...
6. Save & Close.

!Параметры для платёжной системы
callbackURL /payment-mandarin.php
returnURL   /result-mandarin.php


!Тестирование
1. Переходим по %domain%/
2. Main Menu > Переходим по Названию продукта > Add to cart. 
3. Переходим в меню Main menu > checkout. Там заполняем реквизиты, оплачиваем.
4. Проверяем статус заказа в Admin > Components > JoomShopping > Orders.