<?php
$installer = $this;
/* @var $installer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'net_terms', 'TEXT NULL');

$installer->getConnection()->addColumn($this->getTable('sales/order_aggregated_created'), 'net_terms', 'TEXT NULL');

$installer->endSetup();
