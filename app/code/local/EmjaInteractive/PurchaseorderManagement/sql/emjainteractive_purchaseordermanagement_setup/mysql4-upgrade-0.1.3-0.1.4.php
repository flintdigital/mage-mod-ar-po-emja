<?php
$installer = $this;
$installer->startSetup();

$installer->addAttribute('customer', 'po_limit', array(
    'label'         => Mage::helper('emjainteractive_purchaseordermanagement')->__('PO Limit'),
    'visible'       => false,
    'required'      => false,
    'type'          => 'varchar',
    'input'         => 'text',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => false,
    'required'          => false,
    'user_defined'      => true,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'sort_order'    => '2001',
));

$attributeId = $installer->getAttribute('customer', 'po_limit', 'attribute_id');

if ($attributeId) {
    $installer->run("
        INSERT IGNORE INTO {$this->getTable('customer/form_attribute')} VALUES ('adminhtml_customer', {$attributeId});
    ");
    $installer->run("
        INSERT IGNORE INTO {$this->getTable('customer/form_attribute')} VALUES ('adminhtml_checkout', {$attributeId});
    ");    
}

$installer->addAttribute('customer', 'po_credit', array(
    'label'         => Mage::helper('emjainteractive_purchaseordermanagement')->__('PO Credit Left'),
    'visible'       => false,
    'required'      => false,
    'type'          => 'varchar',
    'input'         => 'text',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => false,
    'required'          => false,
    'user_defined'      => true,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'sort_order'    => '2001',
));

$attributeId = $installer->getAttribute('customer', 'po_credit', 'attribute_id');

if ($attributeId) {
    $installer->run("
        INSERT IGNORE INTO {$this->getTable('customer/form_attribute')} VALUES ('adminhtml_customer', {$attributeId});
    ");
    $installer->run("
        INSERT IGNORE INTO {$this->getTable('customer/form_attribute')} VALUES ('adminhtml_checkout', {$attributeId});
    ");    
}

$installer->endSetup();
