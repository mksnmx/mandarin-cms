!Конфигурация Mandarin PM
1. Закидываем архив в корень сайта /
2. %domain%/install-mandarin.php
3. Удалить %domain%/install-mandarin.php
4. %domain$/administrator/ Components > Joomshoping > Options. Payments ("способ оплаты").
5. Кликаем на MandarinBank, важна вторая закладка
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