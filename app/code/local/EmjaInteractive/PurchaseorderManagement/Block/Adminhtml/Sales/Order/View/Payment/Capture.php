<?php

class EmjaInteractive_PurchaseorderManagement_Block_Adminhtml_Sales_Order_View_Payment_Capture
    extends Mage_Adminhtml_Block_Sales_Order_Payment
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('emjainteractive/purchaseordermanagement/sales/order/view/payment/capture.phtml');
    }

    public function setPayment($payment)
    {
        $paymentInfoBlock = false;
        if ($payment->getMethod() == 'checkmo') {
            $paymentInfoBlock = $this->getLayout()->createBlock('emjainteractive_purchaseordermanagement/payment_info_checkmo_capture')
                ->setInfo($payment);
        } else {
            $paymentInfoBlock = Mage::helper('payment')->getInfoBlock($payment);
        }
        $this->setChild('info', $paymentInfoBlock);
        $this->setData('payment', $payment);
        return $this;
    }

    public function getOrder()
    {
        if (Mage::registry('current_order')) {
            return Mage::registry('current_order');
        }

        if (Mage::registry('current_invoice')) {
            return Mage::registry('current_invoice')->getOrder();
        }

        if (Mage::registry('current_creditmemo')) {
            return Mage::registry('current_creditmemo')->getOrder();
        }
    }

    protected function _beforeToHtml()
    {
        if (!$this->getParentBlock()) {
            Mage::throwException(Mage::helper('adminhtml')->__('Invalid parent block for this block'));
        }

        $payment = Mage::helper('emjainteractive_purchaseordermanagement/payment')->getCapturePayment($this->getOrder());

        if ($payment->getId()) {
            $this->setPayment($payment);
        } else {
            $this->setData('payment', $payment);
        }

        Mage_Adminhtml_Block_Template::_beforeToHtml();
    }

    protected function _toHtml()
    {
        if ($this->getPayment()->getId()) {
            return Mage_Adminhtml_Block_Template::_toHtml();
        } else {
            return '';
        }

    }
}