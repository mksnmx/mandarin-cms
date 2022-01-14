
DROP TABLE IF EXISTS `mandarin_system`;
CREATE TABLE IF NOT EXISTS `mandarin_system` (
  `id` int(11) NOT NULL auto_increment,
  `status` int(11) NOT NULL,
  `title` text NOT NULL,
  `title_end` text NOT NULL,
  `merchant_id` varchar(64) NOT NULL default '',
  `merchant_sig` varchar(64) NOT NULL default '',
  `serial` varchar(64) NOT NULL default '',
  `version` FLOAT(2) DEFAULT '1.1' NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

INSERT INTO `mandarin_system` (`id`, `status`, `title`, `title_end`, `merchant_id`, `merchant_sig`, `serial`, `version`) VALUES
(1, 0, 'Платежная система MandarinBank', 'Нажав кнопку Оплатить счет, Вы перейдете в шлюз оплаты сервиса MANDARINBANK, где Вам будет предложено оплатить заказ любым удобным способом: картами Visa, MasterCard, Яндекс-Деньги, Webmoney, терминалы QIWI', '', '', '', 1.1);

