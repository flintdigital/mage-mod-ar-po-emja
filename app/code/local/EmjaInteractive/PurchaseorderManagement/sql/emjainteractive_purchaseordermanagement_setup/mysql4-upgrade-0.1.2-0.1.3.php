<?php
$installer = $this;
$installer->startSetup();

$attributeId = $this->getAttribute('customer', 'net_terms', 'attribute_id');
if ($attributeId) {
    $installer->run("
        INSERT IGNORE INTO {$this->getTable('customer/form_attribute')} VALUES ('adminhtml_checkout', {$attributeId});
    ");
}

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
    'user_defined'      => true,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'sort_order'        => '2000',
));

$installer->endSetup();