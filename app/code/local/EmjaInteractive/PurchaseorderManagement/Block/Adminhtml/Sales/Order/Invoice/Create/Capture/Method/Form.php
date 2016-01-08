<?php

class EmjaInteractive_PurchaseorderManagement_Block_Adminhtml_Sales_Order_Invoice_Create_Capture_Method_Form
    extends Mage_Payment_Block_Form_Container
{

    protected $_allowedCaptureMethods = null;

    protected function _prepareLayout()
    {
        /**
         * Create child blocks for payment methods forms
         */
        foreach ($this->getMethods() as $method) {

            if ($method->getCode() == 'checkmo') {
                $this->setChild(
                    'payment.method.'.$method->getCode(),
                    $this->getLayout()->createBlock('emjainteractive_purchaseordermanagement/payment_form_checkmo_capture')
                    ->setMethod($method)
                );
            } else {
                $this->setChild(
                   'payment.method.'.$method->getCode(),
                   $this->helper('payment')->getMethodFormBlock($method)
                );

            }
        }

        return Mage_Core_Block_Template::_prepareLayout();
    }

    protected function _getAllowedCaptureMethods()
    {
        if (is_null($this->_allowedCaptureMethods)) {
            if (strpos(Mage::getStoreConfig('payment/purchaseorder/capture_methods'), ',') !== false) {
                $this->_allowedCaptureMethods = explode(',', Mage::getStoreConfig('payment/purchaseorder/capture_methods'));
            } else {
                $this->_allowedCaptureMethods = array(Mage::getStoreConfig('payment/purchaseorder/capture_methods'));
            }
        }

        return $this->_allowedCaptureMethods;
    }

    public function getInvoice()
    {
        return Mage::registry('current_invoice');
    }

    public function getOrder()
    {
        return $this->getInvoice()->getOrder();
    }


    protected function _canUseMethod($method)
    {
        if (!in_array($method->getCode(), $this->_getAllowedCaptureMethods())) {
            return false;
        }

        if (!$method->canUseForCountry($this->getOrder()->getBillingAddress()->getCountry())) {
            return false;
        }

        if (!$method->canUseForCurrency(Mage::app()->getStore()->getBaseCurrencyCode())) {
            return false;
        }

        /**
         * Checking for min/max order total for assigned payment method
         */
        $total = $this->getInvoice()->getBaseGrandTotal();
        $minTotal = $method->getConfigData('min_order_total');
        $maxTotal = $method->getConfigData('max_order_total');

        if((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
            return false;
        }
        return true;
    }    

    protected function _assignMethod($method)
    {
        $method->setInfoInstance($this->getOrder()->getPayment());
        return $this;
    }
    
    public function getMethods()
    {
        $methods = $this->getData('methods');
        if (is_null($methods)) {
            $methods = $this->helper('emjainteractive_purchaseordermanagement/payment')->getCaptureMethods();
            foreach ($methods as $key => $method) {
                if ($this->_canUseMethod($method)) {
                    $this->_assignMethod($method);
                } else {
                    unset($methods[$key]);
                }
            }
            $this->setData('methods', $methods);
        }
        return $methods;
    }
}