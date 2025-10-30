// Регистрируем способ оплаты для WooCommerce Blocks
(function() {
    'use strict';

    // Получаем необходимые функции из WooCommerce
    const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
    const { getSetting } = window.wc.wcSettings;

    // Получаем настройки плагина, переданные с сервера
    const settings = getSetting('mandarin_pay_data', {});

    // Создаем лейбл для способа оплаты
    const label = wp.element.createElement('span', {
        style: { display: 'flex', alignItems: 'center' }
    }, settings.title || 'Mandarin');

    // Компонент для отображения описания
    const Content = () => {
        return wp.element.createElement('div', {
            dangerouslySetInnerHTML: {
                __html: settings.description || 'Оплата через платежную систему Mandarin'
            }
        });
    };

    // Регистрируем способ оплаты
    registerPaymentMethod({
        name: 'mandarin_pay',
        label: label,
        content: wp.element.createElement(Content),
        edit: wp.element.createElement(Content),
        canMakePayment: () => true, // Всегда доступен
        ariaLabel: settings.title || 'Mandarin',
        supports: {
            features: ['products'] // Поддерживаем обычные товары
        }
    });

    console.log('Mandarin payment method registered for blocks');
})();
