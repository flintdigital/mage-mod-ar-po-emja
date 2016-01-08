<?php

class EmjaInteractive_PurchaseorderManagement_Model_Adminhtml_Sales_Order_Creditmemo_Create_Observer
{
    public function beforeHtml($observer)
    {
        if ($observer->getBlock() instanceof Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create_Items) {

            $order = $observer->getBlock()->getCreditmemo()->getOrder();
            if (!Mage::helper('emjainteractive_purchaseordermanagement')->isPurchaseOrder($order)) {
                return $this;
            }

            $submitButton = ($observer->getBlock()->getChild('submit_button')) ?
                $observer->getBlock()->getChild('submit_button') :
                $observer->getBlock()->getChild('submit_offline');
            if ($submitButton) {
                $submitButton->setLabel(Mage::helper('sales')->__('Refund'));
            }
        }

        return $this;
    }
}