<?php

class EmjaInteractive_PurchaseorderManagement_Model_Mysql4_Sales_Order_Capture_Payment
    extends Mage_Sales_Model_Mysql4_Order_Payment
{
    protected function _construct()
    {
        $this->_init('emjainteractive_purchaseordermanagement/capture_payment', 'entity_id');
    }

}