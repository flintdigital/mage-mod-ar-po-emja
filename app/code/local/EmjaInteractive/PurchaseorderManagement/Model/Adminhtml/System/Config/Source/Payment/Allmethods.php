<?php

class EmjaInteractive_PurchaseorderManagement_Model_Adminhtml_System_Config_Source_Payment_Allmethods
    extends Mage_Adminhtml_Model_System_Config_Source_Payment_Allmethods
{
    /**
     * Retrieve method model object
     *
     * @param   string $code
     * @return  Mage_Payment_Model_Method_Abstract|false
     */
    public function getMethodInstance($code)
    {
        $key = Mage_Payment_Helper_Data::XML_PATH_PAYMENT_METHODS.'/'.$code.'/model';
        $class = Mage::getStoreConfig($key);
        return Mage::getModel($class);
    }

    /**
     * Retrieve all payment methods
     *
     * @param mixed $store
     * @return array
     */
    public function getPaymentMethods($store = null)
    {
        return Mage::getStoreConfig(Mage_Payment_Helper_Data::XML_PATH_PAYMENT_METHODS, $store);
    }

    public function toOptionArray()
    {
        $methods = array();
        $groupRelations = array();

        $disallowedPaymentMethods = array(
            'paypal_billing_agreement', 'googlecheckout', 'free', 'purchaseorder', 'paypal_billing_agreement',
        );

        foreach ($this->getPaymentMethods(null) as $code => $data) {
            if (in_array($code, $disallowedPaymentMethods)) {
                continue;
            }
            $methodInstance = $this->getMethodInstance($code);
            if ($methodInstance) {
                $checkoutRedirectUrl = $methodInstance->getCheckoutRedirectUrl();
                if (empty($checkoutRedirectUrl)) {
                    $methods[$code] = $methodInstance->getConfigData('title', null);
                }
            }
            if (isset($data['group'])) {
                $groupRelations[$code] = $data['group'];
            }
        }
        $groups = Mage::app()->getConfig()->getNode(Mage_Payment_Helper_Data::XML_PATH_PAYMENT_GROUPS)->asCanonicalArray();
        foreach ($groups as $code => $title) {
            $methods[$code] = $title; // for sorting, see below
        }
        asort($methods);
        $labelValues = array();
        foreach ($methods as $code => $title) {
            $labelValues[$code] = array();
        }
        foreach ($methods as $code => $title) {
            if (isset($groups[$code])) {
                $labelValues[$code]['label'] = $title;
            } elseif (isset($groupRelations[$code])) {
                unset($labelValues[$code]);
                $labelValues[$groupRelations[$code]]['value'][$code] = array('value' => $code, 'label' => $title);
            } else {
                $labelValues[$code] = array('value' => $code, 'label' => $title);
            }
        }
        return $labelValues;
    }
}