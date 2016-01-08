<?php
$installer = $this;
$installer->startSetup();

$installer->run("

CREATE TABLE `{$this->getTable('emjainteractive_purchaseordermanagement/capture_payment_transaction')}` (
  `transaction_id` int(10) unsigned NOT NULL auto_increment,
  `parent_id` int(10) unsigned default NULL,
  `order_id` int(10) unsigned NOT NULL default '0',
  `payment_id` int(10) unsigned NOT NULL default '0',
  `txn_id` varchar(100) NOT NULL default '',
  `parent_txn_id` varchar(100) default NULL,
  `txn_type` varchar(15) NOT NULL default '',
  `is_closed` tinyint(1) unsigned NOT NULL default '1',
  `additional_information` blob,
  `created_at` datetime default NULL,
  PRIMARY KEY  (`transaction_id`),
  UNIQUE KEY `UNQ_ORDER_CAPTURE_PAYMENT_TXN` (`order_id`,`payment_id`,`txn_id`),
  KEY `IDX_ORDER_ID` (`order_id`),
  KEY `IDX_PARENT_ID` (`parent_id`),
  KEY `IDX_PAYMENT_ID` (`payment_id`),
  CONSTRAINT `FK_SALES_CAPTURE_PAYMENT_TRANSACTION_ORDER` FOREIGN KEY (`order_id`) REFERENCES `{$this->getTable('sales/order')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_SALES_CAPTURE_PAYMENT_TRANSACTION_PARENT` FOREIGN KEY (`parent_id`) REFERENCES `{$this->getTable('emjainteractive_purchaseordermanagement/capture_payment_transaction')}` (`transaction_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_SALES_CAPTURE PAYMENT_TRANSACTION_PAYMENT` FOREIGN KEY (`payment_id`) REFERENCES `{$this->getTable('emjainteractive_purchaseordermanagement/capture_payment')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


");
$installer->endSetup();