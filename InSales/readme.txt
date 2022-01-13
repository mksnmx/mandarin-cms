Необходимо получить авторизационные данные в системе Mandarin.

Далее в Insales вам необходимо добавить новый способ оплаты: "Внешний способ оплаты".

В появившемся меню необходимо ввести:

    Значение MerchantID в качестве идентификатора магазина.
    Значение Secret в качестве пароля.
    Значение http://pay.mandarinbank.com/mandarin_proxy.php в качестве внешней ссылки.

Параметры MerchantID и Secret можно узнать в личном кабинете (https://docs.mandarin.io/pay-russian/tools.html#%D0%B0%D1%83%D1%82%D0%B5%D0%BD%D1%82%D0%B8%D1%84%D0%B8%D0%BA%D0%B0%D1%86%D0%B8%D0%BE%D0%BD%D0%BD%D1%8B%D0%B5-%D0%B4%D0%B0%D0%BD%D0%BD%D1%8B%D0%B5).

Затем нужно перейти по ссылке в личный кабинет (https://admin.mandarinpay.com/user)
(opens new window) и прописать в поле Callback url значение "URL для перехода при успешной оплате" из Insales.
