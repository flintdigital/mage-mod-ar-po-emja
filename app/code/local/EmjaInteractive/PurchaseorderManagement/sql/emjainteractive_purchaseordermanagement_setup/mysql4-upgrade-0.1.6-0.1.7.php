<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('sales/order_grid'), 'po_number', 'VARCHAR(255) NULL');
$this->getConnection()->addKey(
    $this->getTable('sales/order_grid'),
    'po_number',
    'po_number'
);

$select = $this->getConnection()->select();
$select->join(
    array('payment'=>$this->getTable('sales/order_payment')),
    'payment.parent_id = order_grid.entity_id',
    array('po_number' => 'po_number')
);
$this->getConnection()->query(
    $select->crossUpdateFromSelect(
        array('order_grid' => $this->getTable('sales/order_grid'))
    )
);

$installer->endSetup();