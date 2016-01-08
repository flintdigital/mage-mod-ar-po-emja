<?php

class EmjaInteractive_PurchaseorderManagement_Block_Adminhtml_Sales_Order_Invoice_Create_Form
    extends Mage_Adminhtml_Block_Sales_Order_Invoice_Create_Form
{
    public function getSaveUrl()
    {
        if (Mage::helper('emjainteractive_purchaseordermanagement')->isPurchaseOrder($this->getOrder())) {
            return $this->getUrl('*/po_sales_order_invoice/save', array('order_id' => $this->getInvoice()->getOrderId()));
        }

        return parent::getSaveUrl();
    }
}