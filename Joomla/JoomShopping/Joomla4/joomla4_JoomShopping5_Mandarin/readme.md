Установка плагина
1. Распаковываем архив в корень сайта
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
