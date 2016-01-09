<?php
class EmjaInteractive_PurchaseorderManagement_Block_Payment_Info_Purchaseorder
    extends Mage_Payment_Block_Info_Purchaseorder
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('emjainteractive/purchaseordermanagement/payment/info/purchaseorder.phtml');
    }

    /* (non-PHPdoc)
     * @see Mage_Payment_Block_Info_Purchaseorder::toPdf()
     */
    public function toPdf()
    {
        $this->setTemplate('emjainteractive/purchaseordermanagement/payment/info/pdf/purchaseorder.phtml');
        return $this->toHtml();
    }

    /**
     * Enter description here ...
     * @return string
     */
    public function getNetTerms()
    {
        $netTerms = '';
        if ($this->getInfo()->getOrder()) {
            $netTerms =  $this->getInfo()->getOrder()->getNetTerms();
        }
        return $netTerms;
    }
}
