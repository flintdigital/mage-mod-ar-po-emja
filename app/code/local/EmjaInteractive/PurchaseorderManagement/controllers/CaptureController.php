<?php

class EmjaInteractive_PurchaseorderManagement_CaptureController extends Mage_Core_Controller_Front_Action
{
    /**
     * Pre dispatch method
     *
     * @return $this
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!$this->_getSession()->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
        return $this;
    }

    /**
     * Get customer session
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Get catalog session
     *
     * @return Mage_Catalog_Model_Session
     */
    protected function _getCatalogSession()
    {
        return Mage::getSingleton('catalog/session');
    }

    /**
     * Get helper
     *
     * @return EmjaInteractive_PurchaseorderManagement_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('emjainteractive_purchaseordermanagement');
    }

    /**
     * Init invoice
     *
     * @return bool|Mage_Sales_Model_Order_Invoice
     * @throws Mage_Core_Exception
     */
    protected function _initInvoice()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        if (!$order || !$order->getId() || ($order->getCustomerId() != $this->_getSession()->getCustomer()->getId())) {
            $this->_getCatalogSession()->addError($this->__('The order no longer exists.'));
            return false;
        }
        if (!$order->canInvoice() || !$this->_helper()->canMakePayment($order)) {
            $this->_getCatalogSession()->addError($this->__('The order does not allow creating an invoice.'));
            return false;
        }
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice(array());
        if (!$invoice->getTotalQty()) {
            Mage::throwException($this->__('Cannot create an invoice without products.'));
        }
        Mage::register('current_invoice', $invoice);
        return $invoice;
    }

    /**
     * Capture index action
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function indexAction()
    {
        if (!$this->_helper()->enabledFrontCapture() || !$this->_helper()->frontendCaptureAllowedForCustomer(Mage::getSingleton('customer/session')->getCustomer())) {
            return $this->_redirect('sales/order/view', array('order_id' => $this->getRequest()->getParam('order_id')));
        }
        try {
            $invoice = $this->_initInvoice();
            if ($invoice) {
                $invoice->setRequestedCaptureCase('online');
                $paymentInfo = $this->getRequest()->getPost('payment', array());

                if (!$this->_helper()->canMakePayment($invoice->getOrder())) {
                    Mage::throwException($this->__('Payment cannot be applied for this order.'));
                }

                if (is_array($paymentInfo) && count($paymentInfo) && isset($paymentInfo['method'])) {
                    if (!$this->_validatePaymentMethod($invoice->getOrder(), $paymentInfo['method'])) {
                        Mage::throwException($this->__('This payment method could not be used.'));
                    }
                    $capturePayment = Mage::getModel('emjainteractive_purchaseordermanagement/sales_order_capture_payment');
                    $capturePayment->setOrder($invoice->getOrder());
                    $capturePayment->importData($paymentInfo);
                    $capturePayment->setAmountOrdered($invoice->getOrder()->getTotalDue());
                    $capturePayment->setBaseAmountOrdered($invoice->getOrder()->getBaseTotalDue());
                    $capturePayment->setShippingAmount($invoice->getOrder()->getShippingAmount());
                    $capturePayment->setBaseShippingAmount($invoice->getOrder()->getBaseShippingAmount());

                    $capturePayment->setAmountAuthorized($invoice->getOrder()->getTotalDue());
                    $capturePayment->setBaseAmountAuthorized($invoice->getOrder()->getBaseTotalDue());
                    $clonedInvoice = clone $invoice;
                    $invoice->getOrder()->addRelatedObject($capturePayment);
                    if ($capturePayment->canCapture()) {
                        $capturePayment->capture($clonedInvoice);
                        $capturePayment->pay($clonedInvoice);
                    } else {
                        $capturePayment->pay($clonedInvoice);
                    }
                } else {
                    Mage::throwException($this->__('Unable to save the invoice.'));
                }
                $invoice->register();
                $invoice->setEmailSent(true);
                $invoice->getOrder()->setCustomerNoteNotify(true);
                $invoice->getOrder()->setIsInProcess(true);
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $transactionSave->save();
                $this->_getCatalogSession()->addSuccess($this->__('The invoice has been created.'));

                try {
                    $invoice->sendEmail(true, '');
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_getCatalogSession()->addError($this->__('Unable to send the invoice email.'));
                }
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getCatalogSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getCatalogSession()->addError($this->__('Unable to save the invoice.'));
            Mage::logException($e);
        }
        return $this->_redirect('sales/order/view', array('order_id' => $this->getRequest()->getParam('order_id')));
    }

    /**
     * Validate payment method
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    protected function _validatePaymentMethod($order, $methodCode)
    {
        $_allowedMethods = $this->_getAllowedCaptureMethods($order);
        $methods = Mage::helper('emjainteractive_purchaseordermanagement/payment')->getCaptureMethods();
        foreach ($methods as $key => $method) {
            if (($method->getCode() == $methodCode)) {
                if (!in_array($methodCode, $_allowedMethods)) {
                    return false;
                }
                if (!$method->canUseForCountry($order->getBillingAddress()->getCountry())) {
                    return false;
                }
                if (!$method->canUseForCurrency(Mage::app()->getStore()->getBaseCurrencyCode())) {
                    return false;
                }
                $total = $order->getBaseGrandTotal();
                $minTotal = $method->getConfigData('min_order_total');
                $maxTotal = $method->getConfigData('max_order_total');

                if((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
                    return false;
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Get allowed capture methods
     *
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function _getAllowedCaptureMethods($order)
    {
        if (!$this->_helper()->isPurchaseOrder($order)) {
            return array();
        }
        $_configMethods = Mage::getStoreConfig('payment/purchaseorder/frontend_capture_methods');
        if (strpos($_configMethods, ',') !== false) {
            return explode(',', $_configMethods);
        }
        return array($_configMethods);
    }
}