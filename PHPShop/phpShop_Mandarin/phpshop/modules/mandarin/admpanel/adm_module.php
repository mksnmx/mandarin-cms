<?php

// SQL
$PHPShopOrm = new PHPShopOrm($PHPShopModules->getParam("base.mandarin.mandarin_system"));
PHPShopObj::loadClass('order');

// Функция обновления
function actionUpdate() {
    global $PHPShopOrm;

    $PHPShopOrm->debug = false;
    $action = $PHPShopOrm->update($_POST);
    header('Location: ?path=modules&install=check');
    return $action;
}

function actionStart() {
    global $PHPShopGUI, $PHPShopOrm;

    // Выборка
    $data = $PHPShopOrm->select();

    //$Tab1 = $PHPShopGUI->setField('Наименование типа оплаты', $PHPShopGUI->setInputText(false, 'title_new', $data['title']));
    $Tab1.=$PHPShopGUI->setField('Merchant ID', $PHPShopGUI->setInputText(false, 'merchant_id_new', $data['merchant_id'], 300));
    $Tab1.=$PHPShopGUI->setField('Merchant SECRET', $PHPShopGUI->setInputText(false, 'merchant_sig_new', $data['merchant_sig'], 300));

    // Доступые статусы заказов
    $PHPShopOrderStatusArray = new PHPShopOrderStatusArray();
    $OrderStatusArray = $PHPShopOrderStatusArray->getArray();
    $order_status_value[] = array(__('Новый заказ'), 0, $data['status']);
    if (is_array($OrderStatusArray))
        foreach ($OrderStatusArray as $order_status)
            $order_status_value[] = array($order_status['name'], $order_status['id'], $data['status']);

    // Статус заказа
    //$Tab1.= $PHPShopGUI->setField('Оплата при статусе', $PHPShopGUI->setSelect('status_new', $order_status_value, 210));

    $Tab1.=$PHPShopGUI->setField('Описание оплаты', $PHPShopGUI->setTextarea('title_end_new', $data['title_end']));


    // Форма регистрации
    $Tab2 = $PHPShopGUI->setPay(null, false, $data['version'], true);

    // Вывод формы закладки
    $PHPShopGUI->setTab(array("Основное", $Tab1), array("О Модуле", $Tab2));

    // Вывод кнопок сохранить и выход в футер
    $ContentFooter =
            $PHPShopGUI->setInput("hidden", "rowID", $data['id']) .
            $PHPShopGUI->setInput("submit", "saveID", "Применить", "right", 80, "", "but", "actionUpdate.modules.edit");

    $PHPShopGUI->setFooter($ContentFooter);
    return true;
}

// Обработка событий
$PHPShopGUI->getAction();

// Вывод формы при старте
$PHPShopGUI->setLoader($_POST['editID'], 'actionStart');
?>