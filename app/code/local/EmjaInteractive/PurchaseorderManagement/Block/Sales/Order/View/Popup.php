<?php

class EmjaInteractive_PurchaseorderManagement_Block_Sales_Order_View_Popup extends Mage_Core_Block_Template
{
    protected $_allowedCaptureMethods = null;

    /**
     * Prepare layout
     *
     * @return Mage_Core_Block_Abstract
     */
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

        return parent::_prepareLayout();
    }


    /**
     * Get Current Order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * Get Allowed capture methods from config
     *
     * @return string
     */
    protected function _getConfigAllowedCaptureMethods()
    {
        return Mage::getStoreConfig('payment/purchaseorder/frontend_capture_methods');
    }

    /**
     * Get allowed capture methods
     *
     * @return array|mixed
     */
    protected function _getAllowedCaptureMethods()
    {
        if ($this->getOrder()->getPayment()->getMethod() != 'purchaseorder') {
            return array();
        }

        if (is_null($this->_allowedCaptureMethods)) {
            if (strpos($this->_getConfigAllowedCaptureMethods(), ',') !== false) {
                $this->_allowedCaptureMethods = explode(',', $this->_getConfigAllowedCaptureMethods());
            } else {
                $this->_allowedCaptureMethods = array($this->_getConfigAllowedCaptureMethods());
            }
        }

        return $this->_allowedCaptureMethods;
    }

    /**
     * Check if can use method
     *
     * @param Mage_Payment_Model_Method_Abstract $method
     * @return bool
     */
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
        $total = $this->getOrder()->getBaseGrandTotal();
        $minTotal = $method->getConfigData('min_order_total');
        $maxTotal = $method->getConfigData('max_order_total');

        if((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
            return false;
        }
        return true;
    }

    /**
     * Assign method
     *
     * @param Mage_Payment_Model_Method_Abstract $method
     * @return $this
     */
    protected function _assignMethod($method)
    {
        $method->setInfoInstance($this->getOrder()->getPayment());
        return $this;
    }

    /**
     * Get allowed payment methods
     *
     * @return array|mixed
     */
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

    /**
     * Payment method form html getter
     *
     * @param Mage_Payment_Model_Method_Abstract $method
     * @return string
     */
    public function getPaymentMethodFormHtml(Mage_Payment_Model_Method_Abstract $method)
    {
        return $this->getChildHtml('payment.method.' . $method->getCode());
    }

    /**
     * Return method title for payment selection page
     *
     * @param Mage_Payment_Model_Method_Abstract $method
     * @return string
     */
    public function getMethodTitle(Mage_Payment_Model_Method_Abstract $method)
    {
        $form = $this->getChild('payment.method.' . $method->getCode());
        if ($form && $form->hasMethodTitle()) {
            return $form->getMethodTitle();
        }
        return $method->getTitle();
    }

    /**
     * Payment method additional label part getter
     *
     * @param Mage_Payment_Model_Method_Abstract $method
     */
    public function getMethodLabelAfterHtml(Mage_Payment_Model_Method_Abstract $method)
    {
        if ($form = $this->getChild('payment.method.' . $method->getCode())) {
            return $form->getMethodLabelAfterHtml();
        }
    }
}