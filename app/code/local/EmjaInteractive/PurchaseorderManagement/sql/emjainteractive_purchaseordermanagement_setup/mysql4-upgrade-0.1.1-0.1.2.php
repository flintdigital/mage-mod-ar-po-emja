<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($this->getTable('sales/order_grid'), 'payment_method', 'VARCHAR(255) NULL');
$this->getConnection()->addKey(
    $this->getTable('sales/order_grid'),
    'payment_method',
    'payment_method'
);

$select = $this->getConnection()->select();
$select->join(
    array('payment'=>$this->getTable('sales/order_payment')),
    'payment.parent_id = order_grid.entity_id',
    array('payment_method' => 'method')
);
$this->getConnection()->query(
    $select->crossUpdateFromSelect(
        array('order_grid' => $this->getTable('sales/order_grid'))
    )
);
$installer->endSetup();