#Установка
0. Раскрыть архив Joomla3.4-VirtueMart3-plugin-mandarin.zip.
1. Зайти в админ панели "Расширения/менеджер расширений", выбрать архив mandarinbank.zip у себя на компьютере и установить его.
2. Зайти в админку, добавить метод оплаты, выбрать процесс VM-mandarinbank, настроить модуль в соответствии со своим аккаунтом (ID кошелька и секретный ключ)

На сайте Платежной системы необходимо в личном кабинете прописать URL'ы:
a) callbackUrl -> http://SITE_NAME.VASH/index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&pm=mandarinbank&tmpl=component
б) returnUrl  -> задавать не нужно, переопределится системой Joomla

"Сохранить"