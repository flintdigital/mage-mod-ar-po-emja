<?php

$installer = $this;
$installer->startSetup();

$this->updateAttribute('customer', 'po_limit', 'backend_type', 'int');
$this->updateAttribute('customer', 'po_credit', 'backend_type', 'int');

$this->updateAttribute('customer', 'po_limit', 'frontend_class', 'validate-number');
$this->updateAttribute('customer', 'po_credit', 'frontend_class', 'validate-number');

$installer->endSetup();
