<?php

class EmjaInteractive_PurchaseorderManagement_Block_Adminhtml_Sales_Order_Creditmemo_Create_Form
    extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create_Form
{
    public function getSaveUrl()
    {
        if (Mage::helper('emjainteractive_purchaseordermanagement')->isPurchaseOrder($this->getOrder())) {
            return $this->getUrl('*/po_sales_order_creditmemo/save', array('_current' => true));
        }
        return $this->getUrl('*/*/save', array('_current' => true));
    }

}