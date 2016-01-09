<?php

class EmjaInteractive_PurchaseorderManagement_Model_Sales_Order_Observer
{
    protected $_incrementedOrdersId = array();

    /**
     * Saves net terms of PO orders only
     *
     * @param Varien_Event_Observer $observer
     * @return EmjaInteractive_PurchaseorderManagement_Model_Sales_Order_Observer
     */
    public function saveNetTerms($observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (($order instanceof Mage_Sales_Model_Order) and ($order->getPayment()->getMethodInstance()->getCode() == 'purchaseorder')) {
            try {
                $customerEmail = $order->getCustomerEmail();
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId($order->getStore()->getWebsite()->getId())
                    ->loadByEmail($customerEmail);
                
				if ($customer instanceof Mage_Customer_Model_Customer) {
                	$net_terms = $customer->getNetTerms() ? $customer->getNetTerms() : Mage::getStoreConfig('payment/purchaseorder/default_net_terms');
				    $order->setNetTerms($net_terms);
                    foreach($order->getAllItems() as $item) {
                        $item->setNetTerms($net_terms);
                    }
                }

                if (Mage::app()->getStore()->isAdmin()) {
                    $orderPostData = Mage::app()->getRequest()->getParam('order', array());
                    if (isset($orderPostData['account']) && isset($orderPostData['account']['net_terms'])) {
                        $netTerms = $orderPostData['account']['net_terms'];
                        $order->setNetTerms($netTerms);
                        foreach($order->getAllItems() as $item) {
                            $item->setNetTerms($netTerms);
                        }
                    } elseif ($order->getNetTerms()) {
                        $netTerms = $order->getNetTerms();
                        $order->setNetTerms($netTerms);
                        foreach($order->getAllItems() as $item) {
                            $item->setNetTerms($netTerms);
                        }
                    } else {
                        $netTerms = Mage::getStoreConfig('payment/purchaseorder/default_net_terms');
                        $order->setNetTerms($netTerms);
                        foreach($order->getAllItems() as $item) {
                            $item->setNetTerms($netTerms);
                        }
                    }
                }

            } catch (Exception $e) {
                Mage::log($e->getTraceAsString());
            }
        }
        return $this;
    }

    public function addColumnToResource($observer)
    {
        $resource = $observer->getEvent()->getResource();
        $resource->addVirtualGridColumn(
            'payment_method',
            'sales/order_payment',
            array('entity_id' => 'parent_id'),
            'method'
        );        

        $resource->addVirtualGridColumn(
            'po_number',
            'sales/order_payment',
            array('entity_id' => 'parent_id'),
            'po_number'
        );
    }

    public function isPOLimitExceeded($observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getIncreasedCredit()) {
            return;
        }
        if ($order->getId()) {
            return;
        }
        
        $customer = $order->getCustomer();

        if (Mage::app()->getStore()->isAdmin()) {
            $dataHasChanged = false;
            $orderPostData = Mage::app()->getRequest()->getParam('order', array());
            if (isset($orderPostData['account']) && isset($orderPostData['account']['po_limit'])) {
                if ($customer->getPoLimit() != $orderPostData['account']['po_limit']) {
                    $customer->setPoLimit($orderPostData['account']['po_limit']);
                    $dataHasChanged = true;
                }
            }
            if (isset($orderPostData['account']) && isset($orderPostData['account']['po_credit'])) {
                if ($customer->getPoCredit() != $orderPostData['account']['po_credit']) {
                    $customer->setPoCredit($orderPostData['account']['po_credit']);
                    $dataHasChanged = true;
                }
            }

            if ($dataHasChanged) {
                $customer->save();
            }
        }

        if ($customer && $customer->getId() && $order->getPayment()->getMethod() == 'purchaseorder' ) {
            if (!$customer->getPoLimit()) { //set default limit to customer
                $defaultLimit = Mage::getStoreConfig('payment/purchaseorder/default_limit');
                $customer->setPoLimit($defaultLimit)->save();
            }
            $credit = (float) $customer->getPoCredit() + $order->getGrandTotal();

            if ($credit > (float) $customer->getPoLimit()) {
                Mage::throwException(Mage::getStoreConfig('payment/purchaseorder/exceeded_limit_message'));
            }
        } elseif ((!$customer || !$customer->getId()) && $order->getPayment()->getMethod() == 'purchaseorder' ) {
            $defaultLimit = Mage::getStoreConfig('payment/purchaseorder/default_limit');
            $credit = $order->getGrandTotal();

            if ($credit > (float) $defaultLimit) {
                Mage::throwException(Mage::getStoreConfig('payment/purchaseorder/exceeded_limit_message'));
            }
        }
    }

    public function incrementPOCredit($observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (in_array($order->getId(), $this->_incrementedOrdersId)) {
            return false;
        }
        $customer = $order->getCustomer();
        if (!$customer) {
            return;
        }

        if ( $customer->getId() && $order->getPayment()->getMethod() == 'purchaseorder' ) {
            $credit = (float) $customer->getPoCredit() + $order->getGrandTotal();
            $customer->setPoCredit($credit)->save();
            array_push($this->_incrementedOrdersId, $order->getId());
            $order->setIncreasedCredit(true);
        }
    }

    public function decrementPOCreditInvoice($observer)
    {
        $order = $observer->getEvent()->getInvoice()->getOrder();

        if ( $order->getPayment()->getMethod() == 'purchaseorder' ) {
            $customerEmail = $order->getCustomerEmail();
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId($order->getStore()->getWebsite()->getId())
                ->loadByEmail($customerEmail);

            if ($customer->getId()) {
                $credit = (float) $customer->getPoCredit() - $order->getGrandTotal();

                if ($credit < 0) {
                    $credit = 0;
                }
                $customer->setPoCredit($credit)->save();
            }
        }
    }

    public function decrementPOCredit($observer)
    {
        $order = $observer->getEvent()->getItem()->getOrder();

        if ( $order->getPayment()->getMethod() == 'purchaseorder' ) {
            $customerEmail = $order->getCustomerEmail();
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId($order->getStore()->getWebsite()->getId())
                ->loadByEmail($customerEmail);

            if ($customer->getId()) {
                $credit = (float) $customer->getPoCredit() - $order->getGrandTotal();

                if ($credit < 0) {
                    $credit = 0;
                }
                $customer->setPoCredit($credit)->save();
            }
        }
    }


    public function overrideOrderEmailTemplates(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('emjainteractive_purchaseordermanagement');
        $block = $observer->getBlock();
        if ($block && $block->getInfo() && $block->getInfo()->getOrder()) {
            if (!($block->getInfo()->getOrder() instanceof Mage_Sales_Model_Order)) {
                $helper->resetEmailTemplates();
                return $this;
            }
            if (!$helper->isPurchaseOrder($block->getInfo()->getOrder())) {
                $helper->resetEmailTemplates();
                return $this;
            }
        } else {
            $helper->resetEmailTemplates();
            return $this;
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = $block->getInfo()->getOrder();

        foreach (debug_backtrace(0) as $line) {
			if (!isset($line['class'])) {
				continue;
			}
            $emailType = $helper->getEmailCodeByMethod($line['class'], $line['function']);
            switch ($emailType) {
                case 'order':
                    $helper->replaceEmailTemplates($order);
                    break;
                default:
                    break;
            }
        }
    }

    public function overrideOrderEmailTemplatesAfterLoad(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        $helper = Mage::helper('emjainteractive_purchaseordermanagement');
        $helper->resetEmailTemplates();
        if ($helper->isPurchaseOrder($order)) {
            $helper->replaceEmailTemplates($order);
        }
    }
}
