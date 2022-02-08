![Mandarin.io](../../assets/images/Payments_by_color.png#gh-light-mode-only)
![Mandarin.io](../../assets/images/Payments_by_color_bl.png#gh-dark-mode-only)

Позволяет принимать оплату корзины через платежный шлюз Mandarin.

Установка возможна 2-мя способами:

1. Из маркетплейса [1с-bitrix](https://marketplace.1c-bitrix.ru/solutions/mandarinbank.mandarinbank/) 
2. Копированием содержимого архива [bitrix.zip](https://github.com/mksnmx/mandarin-cms/raw/main/Bitrix/mandarinbank.mandarinbank/bitrix.zip) в корень сайта и установкой плагина в разделе Marketplace -> Установленные решения на странице администратора

Настройка модуля:

1. В администрирование сайта, раздел Магазин/Настройки/Платежные системы (bitrix/admin/sale_pay_system.php), нажать "Добавить платежную систему"
2. В поле обработчик выбрать: Раздел пользовательские, платёжная система mandarinbank.mandarinbank
3. Вписать заголовок, тип оплаты Безналичный
4. Ниже на вкладке "По умолчанию", заполнить поля:
* Сумма к оплате: Заказ : Стоимость заказа
* Номер заказа: Заказ : Код заказа(ID)
* Email покупателя: Пользователь : Электронный адрес
* Секретный ключ : Значение : Ваш секретный ключ
* ID кошелька : Значение : Ваш merchant ID

Сохранить.