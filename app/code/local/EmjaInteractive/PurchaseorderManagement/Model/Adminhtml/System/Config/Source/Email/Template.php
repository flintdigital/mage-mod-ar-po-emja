<?php

class EmjaInteractive_PurchaseorderManagement_Model_Adminhtml_System_Config_Source_Email_Template
    extends Varien_Object
{
    const XML_PATH_EMAIL_TEMPLATE = 'payment/purchaseorder/email_template';
    const XML_PATH_GUEST_EMAIL_TEMPLATE = 'payment/purchaseorder/guest_email_template';

    /**
     * Generate list of email templates
     *
     * @return array
     */
    public function toOptionArray()
    {
        if(!$collection = Mage::registry('config_system_email_template')) {
            $collection = Mage::getResourceModel('core/email_template_collection')
                ->load();

            Mage::register('config_system_email_template', $collection);
        }
        $options = $collection->toOptionArray();

        array_unshift(
            $options,
            array(
                'value'=> '',
                'label' => Mage::helper('adminhtml')->__('Order Default Template')
            )
        );

        return $options;
    }
}