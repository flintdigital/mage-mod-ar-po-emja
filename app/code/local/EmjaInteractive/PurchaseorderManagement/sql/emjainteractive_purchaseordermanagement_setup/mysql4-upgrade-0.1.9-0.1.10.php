<?php
$installer = $this;
$installer->startSetup();

$this->updateAttribute('customer', 'net_terms', 'is_user_defined', '0');
$this->updateAttribute('customer', 'po_limit', 'is_user_defined', '0');
$this->updateAttribute('customer', 'po_credit', 'is_user_defined', '0');

$this->updateAttribute('customer', 'net_terms', 'sort_order', '200');
$this->updateAttribute('customer', 'po_limit', 'sort_order', '210');
$this->updateAttribute('customer', 'po_credit', 'sort_order', '220');

$installer->endSetup();