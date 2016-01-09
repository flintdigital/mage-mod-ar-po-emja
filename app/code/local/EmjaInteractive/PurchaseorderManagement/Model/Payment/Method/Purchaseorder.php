<?php

class EmjaInteractive_PurchaseorderManagement_Model_Payment_Method_Purchaseorder
    extends Mage_Payment_Model_Method_Purchaseorder
{
    public function isAvailable($quote = null)
    {
        $isAvailable = parent::isAvailable($quote);

        Mage::getSingleton('core/session', array('name' => 'adminhtml'));
        $apiRunning = Mage::getSingleton('api/server')->getAdapter() != null;
        if ($apiRunning || Mage::getSingleton('admin/session')->isLoggedIn()) {
            return $isAvailable;
        }

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $helper     = Mage::helper('emjainteractive_purchaseordermanagement');
            $customer	= Mage::getSingleton('customer/session')->getCustomer();
            $poLimit	= $customer->getPoLimit() ? $customer->getPoLimit() : $helper->getDefaultPoLimit();
            $cartTotal	= Mage::getModel('checkout/cart')->getQuote()->getGrandTotal();

            $allowedCustomerGroups = $helper->getAllowedCustomerGroups();
            $allowedCustomerGroups = explode(',', $allowedCustomerGroups);
            if(!in_array($customer->getGroupId(), $allowedCustomerGroups)) {
                $isAvailable = false;
            }

            if (!$poLimit) {
                $isAvailable = false;
            } elseif ($poLimit < $cartTotal) {
                $isAvailable = false;
            }
        } else {
            $isAvailable = false;
        }

        return $isAvailable;
    }
}
