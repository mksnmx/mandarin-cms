1. Скопировать содержимое директории MandarinBank в корень сайта.
2. Заходим в админ панель, в пункте меню Extensions выбираем Payments.
3. Находим Payment Method Mandarinbank, устанавливаем.
4. В меню System/Users/User Groups, редактируем группу Top Administrator - выдаем доступы до payment/mandarinbank (если их там нет), сохраняем.
5. В пункте меню Extensions/Payments на строке Mandarinbank нажимаем справа Edit, заполняем Merchant ID и secret (Signature), включаем модуль (Enabled).
6. В личном кабинете банка заполнить callback url http://вашдомен/index.php?route=payment/mandarinbank/callback
7. В личном кабинете банка заполнить return url http://вашдомен/index.php?route=checkout/success