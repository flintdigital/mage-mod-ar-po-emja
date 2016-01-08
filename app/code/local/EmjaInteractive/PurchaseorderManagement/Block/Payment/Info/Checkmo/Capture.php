<?php

class EmjaInteractive_PurchaseorderManagement_Block_Payment_Info_Checkmo_Capture
    extends Mage_Payment_Block_Info_Checkmo
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('emjainteractive/purchaseordermanagement/payment/info/checkmo/capture.phtml');
    }
}