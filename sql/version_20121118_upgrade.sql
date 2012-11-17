---
--- Upgrade 20121118
---

ALTER TABLE `users` CHANGE `ipaddress` `ipaddress` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
ALTER TABLE `users_sessions` CHANGE `ipaddress` `ipaddress` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;

UPDATE `config` SET `value` = '20121118' WHERE `config`.`name` = 'SCHEMA_VERSION';


