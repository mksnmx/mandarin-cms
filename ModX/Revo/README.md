![Mandarin.io](../assets/images/Payments_by_color.png#gh-light-mode-only)
![Mandarin.io](../assets/images/Payments_by_color_bl.png#gh-dark-mode-only)

<b>Mandarin Payment integration for CMS MODX (Revo) MiniShop2</b>

Позволяет принимать оплату через платежный шлюз Mandarin.

* Установка модуля

1. Распакуйте архив [mandarin.zip](https://github.com/mksnmx/mandarin-cms/raw/main/ModX/Revo/mandarin.zip);
2. Скопируйте из архива каталог assets и core в корень системы MODX.
3. В административной панели Modx пройдите в раздел <strong>"Приложения" -> "Minishop2" -> "Настройки" -> "Способы оплаты" -> "Создать"</strong>;
4. Заполните все поля, в поле Класс-обработчик указать <strong>Mandarin</strong>.

* Настройка модуля
1. Открыть файл для редактирования 
<strong>core/components/minishop2/custom/payment/mandarin.class.php</strong>;
2. В верху заполнить поля:
    - MANDARIN_SECRET - Укажите секретный код (Secret) из личного кабинета Mandarin;
    - MANDARIN_MERCHANTID - Идентификатор (Merchant id) из личного кабинета Mandarin;
    - MANDARIN_SUCCESS_ID - Id страницы успешной оплаты;
    - MANDARIN_CANCEL_ID - Id страницы не успешной оплаты;
3. Сохраните файл.

