<?php

class EmjaInteractive_PurchaseorderManagement_Model_Adminhtml_System_Config_Source_Order_Status_Newprocessing
    extends Mage_Adminhtml_Model_System_Config_Source_Order_Status
{
    protected $_stateStatuses = array(
        Mage_Sales_Model_Order::STATE_NEW,
        Mage_Sales_Model_Order::STATE_PROCESSING
    );

    protected $_excludeStatuses = array(
        'purchaseorder_pending_payment'
    );

    public function toOptionArray()
    {
        if ($this->_stateStatuses) {
            $statuses = Mage::getSingleton('sales/order_config')->getStateStatuses($this->_stateStatuses);
        }
        else {
            $statuses = Mage::getSingleton('sales/order_config')->getStatuses();
        }
        $options = array();
        $options[] = array(
               'value' => '',
               'label' => Mage::helper('adminhtml')->__('-- Please Select --')
            );
        foreach ($statuses as $code=>$label) {
            if (in_array($code, $this->_excludeStatuses)) {
                continue;
            }
            $options[] = array(
               'value' => $code,
               'label' => $label
            );
        }

        return $options;
    }
}