1. Распаковать содержимое архива, переместить папку app в корень сайта.
2. В админ части заходим в меню System/Configuration находим слева раздел Sales, в нем выбираем Payment Methods.
3. В контентой части выбираем способ оплаты Mandarin bank, заполняем merchantId и secret code.
4. Настроить callback url: в кабинете банка callback url будет вида http://{ваш_домен}/index.php/mandarinbank/index/returnurl
returnURL можно использовать http://{ваш_домен}

Если в System/Configuration - Sales - Payment Methods нет нашего нового способа оплаты, нужно очистить кэш