1. Скопировать содержимое директории MandarinBank.
2. Заходим в админ панель переходим в Extensions/Extensions  в центральной части выбираем Payments.
3. Находим мандарин банк, устанавливаем.
4. Заходим System/Users/User Groups редактируем группу Administrator выдаем доступы до  payment/mandarinbank 
5. Нажимаем Edit переходим и заполняем id (ID проекта) и secret код (поле API ID), включаем модуль.
6. В личном кабинете банка заполнить callback url http://вашдомен/index.php?route=extension/payment/mandarinbank/callback 
7. В личном кабинете банка заполнить return url http://вашдомен/index.php?route=checkout/success