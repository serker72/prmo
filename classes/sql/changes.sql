/* */
DROP TABLE IF EXISTS `invoice`;
CREATE TABLE `invoice` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `acceptance_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `user_confirm_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `confirm_pdate` bigint(20) NOT NULL DEFAULT '0',
  `pdate` bigint(20) unsigned NOT NULL DEFAULT '0',
  `notes` text NOT NULL,
  `is_confirmed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `org_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `manager_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `given_no` varchar(512) NOT NULL DEFAULT '',
  `given_pdate` bigint(20) NOT NULL DEFAULT '0',
  `status_id` bigint(20) unsigned NOT NULL DEFAULT '1',
  `sector_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `restore_pdate` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `acceptance_id` (`acceptance_id`),
  KEY `user_confirm_id` (`user_confirm_id`),
  KEY `pdate` (`pdate`),
  KEY `is_confirmed` (`is_confirmed`),
  KEY `org_id` (`org_id`),
  KEY `manager_id` (`manager_id`),
  KEY `status_id` (`status_id`),
  KEY `restore_pdate` (`restore_pdate`),
  KEY `given_pdate` (`given_pdate`),
  KEY `rep1` (`is_confirmed`,`org_id`),
  KEY `sector_id` (`sector_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=cp1251;

DROP TABLE IF EXISTS `invoice_file`;
CREATE TABLE `invoice_file` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `storage_id` bigint(255) unsigned NOT NULL DEFAULT '0',
  `invoice_id` bigint(255) unsigned NOT NULL DEFAULT '0',
  `user_id` bigint(255) unsigned NOT NULL DEFAULT '0',
  `filename` varchar(512) NOT NULL DEFAULT '',
  `orig_name` varchar(514) NOT NULL DEFAULT '',
  `pdate` bigint(20) unsigned NOT NULL DEFAULT '0',
  `txt` text NOT NULL,
  `folder_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `text_contents` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `storage_id` (`storage_id`),
  KEY `byuser_id` (`user_id`),
  KEY `byinvoice_id` (`invoice_id`),
  KEY `folder_id` (`folder_id`),
  FULLTEXT KEY `text_contents` (`text_contents`),
  FULLTEXT KEY `orig_name` (`orig_name`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=cp1251;

DROP TABLE IF EXISTS `invoice_file_folder`;
CREATE TABLE `invoice_file_folder` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `storage_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `filename` varchar(512) NOT NULL DEFAULT '',
  `pdate` bigint(20) NOT NULL DEFAULT '0',
  `txt` text NOT NULL,
  `org_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `doc_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `storage_id` (`storage_id`),
  KEY `user_id` (`user_id`),
  KEY `org_id` (`org_id`),
  KEY `doc_id` (`doc_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=cp1251;

DROP TABLE IF EXISTS `invoice_view`;
CREATE TABLE `invoice_view` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `col_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ord` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `col_id` (`col_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=cp1251;

DROP TABLE IF EXISTS `invoice_view_field`;
CREATE TABLE `invoice_view_field` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `colname` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `colname` (`colname`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=cp1251;

DROP TABLE IF EXISTS `invoice_notes`;
CREATE TABLE `invoice_notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `pdate` bigint(20) unsigned NOT NULL DEFAULT '0',
  `note` mediumtext NOT NULL,
  `posted_user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `is_auto` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `byuser_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=cp1251;

DROP TABLE IF EXISTS `invoice_position`;
CREATE TABLE `invoice_position` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `position_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `dimension` varchar(255) NOT NULL DEFAULT '',
  `quantity` double(14,3) unsigned NOT NULL DEFAULT '0.000',
  `price` double(20,2) unsigned NOT NULL DEFAULT '0.00',
  `price_pm` double(20,2) NOT NULL DEFAULT '0.00',
  `total` double(20,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `position_id` (`position_id`),
  KEY `name` (`name`),
  KEY `invoice_id_2` (`invoice_id`,`position_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=cp1251;

/* */
DELETE FROM `invoice_view_field`;
INSERT INTO `invoice_view_field` (`id`, `name`, `colname`) VALUES
(1, 'Номер', 'code'),
(2, 'Дата', 'pdate'),
(3, 'Заданная дата', 'given_pdate'),
(4, 'Заданный номер', 'given_no'),
(5, 'Сумма', 'summ'),
(6, 'Покупатель', 'supplier'),
(7, 'Склад', 'sector'),
(8, 'Статус', 'status'),
(9, 'Реализация товара', 'acceptance'),
(10, 'Примечания', 'notes'),
(11, 'Создал', 'crea'),
(12, 'Файлы', 'files');

DELETE FROM `invoice_view`;
INSERT INTO `invoice_view` (`id`, `col_id`, `user_id`, `ord`) VALUES
(1, 1, 0, 10),
(2, 2, 0, 20),
(3, 3, 0, 30),
(4, 4, 0, 40),
(5, 5, 0, 50),
(6, 6, 0, 70),
(7, 7, 0, 60),
(8, 8, 0, 80),
(9, 9, 0, 90),
(10, 10, 0, 100),
(11, 11, 0, 110),
(12, 12, 0, 120),
(13, 13, 0, 130);

INSERT INTO `object_group` (`id`, `name`, `description`, `ord`) VALUES 
('86', 'Инвойсы', 'Раздел "Инвойсы"', '0');

DELETE FROM `object` WHERE `group_id` = 86;
INSERT INTO `object` (`id`, `group_id`, `name`, `description`, `ord`) VALUES 
('1120', '86', 'Инвойсы', 'Раздел "Инвойсы"', '193'), 
('1121', '86', 'Создание инвойса', 'Создание инвойса', '0'), 
('1122', '86', 'Изменение инвойса', 'Изменение инвойса', '0'), 
('1123', '86', 'Утверждение инвойса', 'Утверждение инвойса', '0'),
('1124', '86', 'Снятие утверждения инвойса', 'Снятие утверждения инвойса', '0'),
('1125', '86', 'Аннулирование инвойса', 'Аннулирование инвойса', '0'),
('1126', '86', 'Восстановление инвойса', 'Восстановление инвойса', '0'),
('1127', '86', 'Доступ к инвойсам всех ответственных сотрудников', 'Доступ к инвойсам всех ответственных сотрудников', '0');

INSERT INTO `left_menu_new` (`id`, `parent_id`, `object_id`, `name`, `description`, `url`, `ord`) VALUES 
(71, '28', '1120', 'Инвойсы', 'Раздел "Инвойсы"', 'invoices.php', '0');

INSERT INTO `user_rights` 
SELECT 0 as id, 2 as user_id, 2 as right_id, o.`id` as object_id
FROM `object`o
WHERE o.`group_id` = 86
AND o.`id` NOT IN (SELECT `object_id` FROM `user_rights` WHERE `user_id` = 2);
