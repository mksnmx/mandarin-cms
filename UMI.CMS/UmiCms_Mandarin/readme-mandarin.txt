Порядок инсталляции

1. Разархивировать zip

2. MandarinBankInstall.php и каталог classes залить в корень сайта

3. В сайте заглянуть в /MandarinBank/templates/ и посмотреть, какие там темы (список каталогов)

4. Файл из архива MandarinBank/templates/ThemeName/php/emarket/payment/mandarinBank.phtml положить во все /php/emarket/payment/

5. Открыть http://sitename/MandarinBankInstall.php и увидеть Готово. Если будет ошибка доступа к файлу, то в корне сайта ".htaccess" переименуйте или временно удалите - после выполнения скрипта MandarinBankInstall.php верните его обратно.

6. В админке магазина открыть Модули - Интернет-магазин, закладка Оплата

7. Добавить способ MandarinBank (там же можно вписать любое название этого способа оплаты)

8. Выбрать его и нажать Редактировать

9. Нажать "Показать расширенные поля" - пиктограмма со стрелкой вниз. Там можно изменить название способа оплаты, ввести MerchantId и Секретный ключ банка.
Кнопка "Сохранить и выйти"

10 callbackURL http://sitename/emarket/gateway/
 returnURL http://sitename/

11 Если на сайте магазина появляется надпись об ошибке вида:
 Cannot render template. PHP template file "/usr/home/www/servers/umi-cms.mandarinpay.com/html/templates/demodizzy/php/emarket/payment/mandarinBank.phtml" is not readable.
это значит, что мы не угадали структуру используемой темы. В этом случае файл mandarinBank.phtml (пункт 4) надо подложить в путь, который указан в надписи об ошибке


