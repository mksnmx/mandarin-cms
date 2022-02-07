![Mandarin.io](../../../assets/images/Payments_by_color.png#gh-light-mode-only)
![Mandarin.io](../../../assets/images/Payments_by_color_bl.png#gh-dark-mode-only)

<b>Mandarin Payment integration for JoomShopping</b>

Позволяет принимать оплату корзины компонента JoomShopping через платежный шлюз Mandarin.

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

[Mandarin](https://mandarin.io) – универсальное решение для работы с онлайн-платежами. API Mandarin построено на REST-принципах. С помощью него вы сможете принимать платежи с банковской карты, получать токен карты и использовать его для повторных списаний, делать возвраты, производить выплаты на карты, используя множество опций.

API использует протокол HTTPS и TLS не ниже 1.2 (запросы с HTTP или TLS 1.0, 1.1 будут отклоняться), а значит, подходит для разработки на любом языке программирования, который умеет работать с HTTPS-библиотеками.

API работает с POST и GET-запросами. POST-запросы принимают аргументы в JSON, GET-запросы работают со строками запросов. Ответ всегда будет в JSON, независимо от типа запроса.

API является асинхронным (незначительная часть запросов работает синхронно): на ваш запрос вы синхронно получите идентификатор платежа (запроса), а затем в асинхронном режиме получите callback-уведомление, включающее полученный ранее идентификатор платежа (запроса), а также статус операции и прочие данные по ней.