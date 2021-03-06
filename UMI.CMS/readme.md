![Mandarin.io](../assets/images/Payments_by_color.png#gh-light-mode-only)
![Mandarin.io](../assets/images/Payments_by_color_bl.png#gh-dark-mode-only)

<b>Mandarin Payment integration for UMI.CMS</b>

Позволяет принимать оплату корзины через платежный шлюз Mandarin.

1. Распакуйте архив [UmiCms_Mandarin.zip](https://github.com/mksnmx/mandarin-cms/raw/main/UMI.CMS/UmiCms_Mandarin.zip)
2. В сайте заглянуть в /MandarinBank/templates/ и посмотреть, какие там темы и найдите ту которую используете на сайте
3. Кталог из архива templates/ThemeName переименуйте названием темы которую используете (из предыдущего пункта)
4. Весь распакованный архив (с переименованной папкой templates/ThemeName) скопируйте в корень сайта.
5. Открыть http://sitename/MandarinBankInstall.php и увидеть Готово. Если будет ошибка доступа к файлу, то в корне сайта ".htaccess" переименуйте или временно удалите - после выполнения скрипта MandarinBankInstall.php верните его обратно.
6. В админке магазина открыть Модули - Интернет-магазин, закладка Оплата
7. Добавить способ MandarinBank (там же можно вписать любое название этого способа оплаты)
8. Выбрать его и нажать Редактировать
9. Нажать "Показать расширенные поля" - пиктограмма со стрелкой вниз. Там можно изменить название способа оплаты, ввести MerchantId и Секретный ключ банка.
Кнопка "Сохранить и выйти"

[Mandarin](https://mandarin.io) – универсальное решение для работы с онлайн-платежами. API Mandarin построено на REST-принципах. С помощью него вы сможете принимать платежи с банковской карты, получать токен карты и использовать его для повторных списаний, делать возвраты, производить выплаты на карты, используя множество опций.

API использует протокол HTTPS и TLS не ниже 1.2 (запросы с HTTP или TLS 1.0, 1.1 будут отклоняться), а значит, подходит для разработки на любом языке программирования, который умеет работать с HTTPS-библиотеками.

API работает с POST и GET-запросами. POST-запросы принимают аргументы в JSON, GET-запросы работают со строками запросов. Ответ всегда будет в JSON, независимо от типа запроса.

API является асинхронным (незначительная часть запросов работает синхронно): на ваш запрос вы синхронно получите идентификатор платежа (запроса), а затем в асинхронном режиме получите callback-уведомление, включающее полученный ранее идентификатор платежа (запроса), а также статус операции и прочие данные по ней.
