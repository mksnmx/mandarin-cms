------- Перед установкой плагина --------
1. Создать главную страницу если не создана.
- Вверху ссылка "Site" > Page Name - index > Save или нажать (CTRL + S)
2. Добавить хотя бы один товар в магазин.
- Вверху ссылка "Store" > вкладка "Products" > "New product" : Product name, Price > Save

-------- Установка --------
1. Заливаем файлы из архива в корень сайта.
2. "Store" > "Settings" (справа вверху) > "Payment" (в левой боковой панели) > "Add payment option" (справа вверху) > Mandarin Bank > ID Мерчанта, Secret Мерчанта > Save.
3. И обязательно не забыть создань страницу которая будет служить "Страницей на которую будет ссылаться пользователь после проплаты". "Site" > Page name: result, Page URL: %domain%/result > Save.

-------- Тестирование ---------
1. Перейти по %domain%/shop. Найти товар. Нажать "Buy". Перейти в корзину. Нажать "Process to checkout". Заполнить реквизиты. Выбрать нужный банк. Нажать "Next". Нажать "Place order".
2. Нажить оплатить под словами "Thank you!...".


callbackURL http://%domain%/payment-mandarin.php
returnURL http://%domain%/result
