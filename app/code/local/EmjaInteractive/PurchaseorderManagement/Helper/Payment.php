<?php

class EmjaInteractive_PurchaseorderManagement_Helper_Payment extends Mage_Payment_Helper_Data
{
    public function getCaptureMethods($store = null, $quote = null)
    {
        $res = array();
        foreach ($this->getPaymentMethods($store) as $code => $methodConfig) {
            $prefix = self::XML_PATH_PAYMENT_METHODS . '/' . $code . '/';
            if (!$model = Mage::getStoreConfig($prefix . 'model', $store)) {
                continue;
            }
            $methodInstance = Mage::getModel($model);
            if (!$methodInstance) {
                continue;
            }
            $methodInstance->setStore($store);
            $sortOrder = (int)$methodInstance->getConfigData('sort_order', $store);
            $methodInstance->setSortOrder($sortOrder);
            $res[] = $methodInstance;
        }

        usort($res, array($this, '_sortMethods'));
        return $res;
    }

    public function getCapturePayment(Mage_Sales_Model_Order $order)
    {
        return Mage::getModel('emjainteractive_purchaseordermanagement/sales_order_capture_payment')->load(
            $order->getId(),
            'parent_id'
        )->setOrder($order);
    }
}