<?php
$installer = $this;
/* @var $installer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();

$installer->addAttribute('customer', 'net_terms', array(
    'type'              => 'text',
    'backend'           => '',
    'frontend'          => '',
    'label'             => Mage::helper('emjainteractive_purchaseordermanagement')->__('Net Terms'),
    'input'             => 'text',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => false,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'sort_order'        => '2000',
));

$attributeId = $this->getAttribute('customer', 'net_terms', 'attribute_id');
if ($attributeId) {
    $installer->run("
        INSERT IGNORE INTO {$this->getTable('customer/form_attribute')} VALUES ('adminhtml_customer', {$attributeId});
    ");
}

$status = Mage::getModel('sales/order_status')
    ->setLabel(Mage::helper('emjainteractive_purchaseordermanagement')->__('Shipped, pending payment'))
    ->setStatus(EmjaInteractive_PurchaseorderManagement_Model_Sales_Order_Status::STATUS_PURCHASEORDER_PENDING_PAYMENT)
    ->save();

$status->assignState(Mage_Sales_Model_Order::STATE_PROCESSING, 0);

$installer->getConnection()->addColumn($this->getTable('sales/order'), 'net_terms', 'TEXT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/order_grid'), 'net_terms', 'TEXT NULL');

$installer->endSetup();
