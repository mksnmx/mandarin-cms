![Mandarin.io](../../assets/images/Payments_by_color.png#gh-light-mode-only)
![Mandarin.io](../../assets/images/Payments_by_color_bl.png#gh-dark-mode-only)

<b>Mandarin Payment integration for CMS Drupal 7.x - Ubercart</b>

Позволяет принимать оплату через платежный шлюз Mandarin.

1. Зайдите в файловый менеджер на вашем хостинге или через FTP в директорию /sites/all/modules/
2. Копируем сюда папку модуля (uc_mandarin) из архива [uc_mandarin.zip](https://github.com/mksnmx/mandarin-cms/raw/main/Drupal/Ubercart/uc_mandarin.zip)
3. Устанавливаем модуль (путь http://{сайт}/admin/modules)
4. Переходим к настройке (Store - CONFIGURATION - Payment methods, путь http://{сайт}/admin/store/settings/payment)

Параметры для платёжной системы:
http://{сайт}/uc_mandarinbank/result - callbackURL
http://{сайт}/uc_mandarinbank/return - returnURL

[Mandarin](https://mandarin.io) – универсальное решение для работы с онлайн-платежами. API Mandarin построено на REST-принципах. С помощью него вы сможете принимать платежи с банковской карты, получать токен карты и использовать его для повторных списаний, делать возвраты, производить выплаты на карты, используя множество опций.

API использует протокол HTTPS и TLS не ниже 1.2 (запросы с HTTP или TLS 1.0, 1.1 будут отклоняться), а значит, подходит для разработки на любом языке программирования, который умеет работать с HTTPS-библиотеками.

API работает с POST и GET-запросами. POST-запросы принимают аргументы в JSON, GET-запросы работают со строками запросов. Ответ всегда будет в JSON, независимо от типа запроса.

API является асинхронным (незначительная часть запросов работает синхронно): на ваш запрос вы синхронно получите идентификатор платежа (запроса), а затем в асинхронном режиме получите callback-уведомление, включающее полученный ранее идентификатор платежа (запроса), а также статус операции и прочие данные по ней.

