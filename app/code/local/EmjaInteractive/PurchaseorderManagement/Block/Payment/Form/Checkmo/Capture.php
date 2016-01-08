<?php
class EmjaInteractive_PurchaseorderManagement_Block_Payment_Form_Checkmo_Capture
    extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('emjainteractive/purchaseordermanagement/payment/form/checkmo/capture.phtml');
    }
    
}