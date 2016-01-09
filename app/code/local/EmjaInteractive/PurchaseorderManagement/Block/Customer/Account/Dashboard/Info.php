<?php

class EmjaInteractive_PurchaseorderManagement_Block_Customer_Account_Dashboard_Info extends Mage_Core_Block_Template
{
    /**
     * Get current customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }
}