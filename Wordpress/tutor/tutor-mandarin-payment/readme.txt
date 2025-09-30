=== Tutor Mandarin Payment ===
Contributors: MandarinLtd
Tags: tutor, lms, payment, gateway, mandarin
Requires at least: 5.0
Tested up to: 6.6.2
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Интеграция платежной системы Mandarin для Tutor LMS.

== Description ==

Плагин добавляет возможность принимать платежи через платежную систему Mandarin в Tutor LMS.

Возможности:
* Прием платежей банковскими картами
* Автоматическая обработка статусов платежей через webhook
* Тестовый и боевой режимы работы
* Подробное логирование для отладки

== Installation ==

1. Загрузите папку плагина в директорию `/wp-content/plugins/`
2. Активируйте плагин через меню 'Плагины' в WordPress
3. Убедитесь, что Tutor LMS и Tutor Pro активированы
4. Перейдите в Tutor LMS > Settings > Payments
5. Настройте параметры Mandarin (Merchant ID и Secret Key)

== Configuration ==

После активации плагина:
1. Перейдите в Tutor LMS > Settings > Payments > Mandarin
2. Введите ваш Merchant ID от Mandarin
3. Введите Secret Key от Mandarin
4. Выберите режим работы (тестовый или боевой)
5. Скопируйте Webhook URL и добавьте его в личном кабинете Mandarin

== Frequently Asked Questions ==

= Требуется ли Tutor Pro? =

Да, для работы платежных шлюзов требуется Tutor Pro.

= Поддерживаются ли подписки? =

В текущей версии подписки не поддерживаются, только разовые платежи.

== Changelog ==

= 1.0.0 =
* Первая версия плагина
* Базовая интеграция с Mandarin
* Поддержка тестового и боевого режимов
