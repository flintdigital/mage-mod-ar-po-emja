<?php
class EmjaInteractive_PurchaseorderManagement_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isPurchaseOrder($order)
    {
        return ($order->getPayment()->getMethod() == 'purchaseorder');
    }

    public function getIconMediaPath()
    {
        return Mage::getBaseDir('media') . DS . 'pdfOrder' . DS;
    }

    public function getIconFullPath()
    {
        return $this->getIconMediaPath() . Mage::getStoreConfig('payment/purchaseorder/paid_icon');
    }
}
