<?php

$installer = $this;
$installer->startSetup();

$installer->updateAttribute('customer', 'po_limit', 'backend_type', 'decimal');
$installer->updateAttribute('customer', 'po_credit', 'backend_type', 'decimal');

$installer->endSetup();