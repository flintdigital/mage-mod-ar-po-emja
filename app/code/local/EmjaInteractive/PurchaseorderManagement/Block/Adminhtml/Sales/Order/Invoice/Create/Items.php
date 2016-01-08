<?php

class EmjaInteractive_PurchaseorderManagement_Block_Adminhtml_Sales_Order_Invoice_Create_Items
    extends Mage_Adminhtml_Block_Sales_Order_Invoice_Create_Items
{
    public function canCapture()
    {
        if (Mage::helper('emjainteractive_purchaseordermanagement')->isPurchaseOrder($this->getInvoice()->getOrder())) {
            return true;
        }

        return parent::canCapture();
    }

}