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
            if (empty($netTerms)) {
                if ($this->getInfo()->getOrder()->getCustomer()) {
                    $customer = $this->getInfo()->getOrder()->getCustomer();
                } else if (!$this->getInfo()->getOrder()->getCustomer() && $this->getInfo()->getOrder()->getCustomerId()) {
                    $customer = Mage::getModel('customer/customer')->load($this->getInfo()->getOrder()->getCustomerId());
                }
                if (isset($customer) && $customer->getId()) {
                    $netTerms = $customer->getNetTerms()
                        ? $customer->getNetTerms()
                        : Mage::getStoreConfig('payment/purchaseorder/default_net_terms');
                }
            }
        }
        if (empty($netTerms) && $this->getInfo()->getQuote()) {
            $netTerms =  $this->getInfo()->getQuote()->getNetTerms();
            if (empty($netTerms) && $this->getInfo()->getQuote()->getCustomer()) {
                $netTerms = $this->getInfo()->getQuote()->getCustomer()->getNetTerms()
                    ? $this->getInfo()->getQuote()->getCustomer()->getNetTerms()
                    : Mage::getStoreConfig('payment/purchaseorder/default_net_terms');
            }
        }
        return $netTerms;
    }
}
