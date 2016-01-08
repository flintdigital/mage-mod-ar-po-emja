<?php

class EmjaInteractive_PurchaseorderManagement_Model_Adminhtml_System_Config_Source_Order_Payment_Method
{
    public function getPaymentMethods()
    {
        $paymentMethods = Mage::getStoreConfig(Mage_Payment_Helper_Data::XML_PATH_PAYMENT_METHODS);
        $result = array();
        foreach($paymentMethods as $code => $paymentData) {
            $result[$code] = (isset($paymentData['title']) ? $paymentData['title'] : '');
        }
        return $result;
    }
}