<?php

class EmjaInteractive_PurchaseorderManagement_Block_Adminhtml_Sales_Order_View_Payment_Edit
    extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('emjainteractive/purchaseordermanagement/sales/order/view/payment/edit.phtml');
    }

    public function getChangePoNumberUrl()
    {
        return $this->getUrl('*/po_sales_order/savePayment', array('_current' => true));
    }

    /**
     * Get current order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * Get order payment
     *
     * @return Mage_Sales_Model_Order_Payment|null
     */
    public function getPayment()
    {
        if ($this->getOrder() && $this->getOrder()->getPayment()) {
            return $this->getOrder()->getPayment();
        }
        return null;
    }

    protected function _toHtml()
    {
        if (!$this->getPayment()) {
            return '';
        }
        if ($this->getPayment()->getMethod() != 'purchaseorder') {
            return '';
        }
        return parent::_toHtml();
    }
}