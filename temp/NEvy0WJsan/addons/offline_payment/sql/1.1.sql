ALTER TABLE `wallets` ADD `approval` INT(1) NOT NULL DEFAULT '0' AFTER `payment_details`, ADD `offline_payment` INT(1) NOT NULL DEFAULT '0' AFTER `approval`;
ALTER TABLE `wallets` ADD `reciept` VARCHAR(150) NULL DEFAULT NULL AFTER `offline_payment`;

COMMIT;