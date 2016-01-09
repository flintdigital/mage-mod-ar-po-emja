<?php

class EmjaInteractive_PurchaseorderManagement_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected static $_orderEmailTemplate = array();
    protected static $_orderGuestEmailTemplate = array();

    /**
     * Check if order has PO payment method
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function isPurchaseOrder($order)
    {
        if ($order->getPayment()) {
            return ($order->getPayment()->getMethod() == 'purchaseorder');
        }
        return false;
    }

    /**
     * Check if can make payment for order
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function canMakePayment($order)
    {
        if ($order->getPayment() && ($order->getPayment()->getMethod() == 'purchaseorder') && $order->canShip()) {
            return false;
        }
        if ($order->getStatus() != 'purchaseorder_pending_payment') {
            return false;
        }
        if (!$order->canInvoice()) {
            return false;
        }
        return true;
    }

    /**
     * Check if enabled frontend checkout
     */
    public function enabledFrontCapture()
    {
        return Mage::getStoreConfigFlag('payment/purchaseorder/enable_frontend');
    }

    /**
     * Check f allowed for customer group
     *
     * @param Mage_Customer_Model_Customer$customer
     * @return bool
     */
    public function frontendCaptureAllowedForCustomer($customer)
    {
        $groups = explode(',', Mage::getStoreConfig('payment/purchaseorder/frontend_customer_groups'));
        if (in_array($customer->getGroupId(), $groups)) {
            return true;
        }
        return false;
    }

    public function getIconMediaPath()
    {
        return Mage::getBaseDir('media') . DS . 'pdfOrder' . DS;
    }

    public function getIconFullPath()
    {
        return $this->getIconMediaPath() . Mage::getStoreConfig('payment/purchaseorder/paid_icon');
    }

    public function canSendPdf($order)
    {
        $attachmentEnabled = Mage::getStoreConfigFlag('payment/purchaseorder/send_po_invoice_attached');
        if ($attachmentEnabled && $this->isPurchaseOrder($order)) {
            return true;
        }
        return false;
    }

    public function isSubclassOf($object, $className)
    {
        $parents = array_values(class_parents($object));
        $parents[] = $object;

        if (in_array($className, $parents)) {
            return true;
        }
        return false;
    }

    public function getEmailCodeByMethod($className, $methodName)
    {
        $code = '';
        if ($this->isSubclassOf($className, 'Mage_Sales_Model_Order_Creditmemo')) {
            $code = 'Mage_Sales_Model_Order_Creditmemo::' . $methodName;
        } else if ($this->isSubclassOf($className, 'Mage_Sales_Model_Order_Invoice')) {
            $code = 'Mage_Sales_Model_Order_Invoice::' . $methodName;
        } else if ($this->isSubclassOf($className, 'Mage_Sales_Model_Order')) {
            $code = 'Mage_Sales_Model_Order::' . $methodName;
        } else if ($this->isSubclassOf($className, 'Mage_Sales_Model_Order_Shipment')) {
            $code = 'Mage_Sales_Model_Order_Shipment::' . $methodName;
        }

        $emailCodes = array(
            'Mage_Sales_Model_Order_Creditmemo::sendEmail' => 'creditmemo',
            'Mage_Sales_Model_Order_Creditmemo::sendUpdateEmail' => 'creditmemo_update',
            'Mage_Sales_Model_Order_Invoice::sendEmail' => 'invoice',
            'Mage_Sales_Model_Order_Invoice::sendUpdateEmail' => 'invoice_update',
            'Mage_Sales_Model_Order::sendNewOrderEmail' => 'order',
            'Mage_Sales_Model_Order::sendOrderUpdateEmail' => 'order_update',
            'Mage_Sales_Model_Order_Shipment::sendEmail' => 'shipment',
            'Mage_Sales_Model_Order_Shipment::sendUpdateEmail' => 'shipment_update'
        );

        if (isset($emailCodes[$code])) {
            return $emailCodes[$code];
        }
        return false;
    }

    public function replaceEmailTemplates($order)
    {
        if (!isset(self::$_orderEmailTemplate[$order->getStoreId()])) {
            self::$_orderEmailTemplate[$order->getStoreId()] = Mage::getStoreConfig(
                Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE,
                $order->getStoreId()
            );
        }
        if (!isset(self::$_orderGuestEmailTemplate[$order->getStoreId()])) {
            self::$_orderGuestEmailTemplate[$order->getStoreId()] = Mage::getStoreConfig(
                Mage_Sales_Model_Order::XML_PATH_EMAIL_GUEST_TEMPLATE,
                $order->getStoreId()
            );
        }
        $emailTemplate = Mage::getStoreConfig(
            EmjaInteractive_PurchaseorderManagement_Model_Adminhtml_System_Config_Source_Email_Template::XML_PATH_EMAIL_TEMPLATE,
            $order->getStoreId()
        );
        $guestEmailTemplate = Mage::getStoreConfig(
            EmjaInteractive_PurchaseorderManagement_Model_Adminhtml_System_Config_Source_Email_Template::XML_PATH_GUEST_EMAIL_TEMPLATE,
            $order->getStoreId()
        );
        if (!empty($emailTemplate)) {
            Mage::app()->getStore($order->getStoreId())->setConfig(
                Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE,
                $emailTemplate
            );
        }
        if (!empty($guestEmailTemplate)) {
            Mage::app()->getStore($order->getStoreId())->setConfig(
                Mage_Sales_Model_Order::XML_PATH_EMAIL_GUEST_TEMPLATE,
                $guestEmailTemplate
            );
        }
    }

    public function resetEmailTemplates()
    {
        foreach (self::$_orderEmailTemplate as $storeId => $value) {
            Mage::app()->getStore($storeId)->setConfig(
                Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE,
                self::$_orderEmailTemplate[$storeId]
            );
        }
        foreach (self::$_orderGuestEmailTemplate as $storeId => $value) {
            Mage::app()->getStore($storeId)->setConfig(
                Mage_Sales_Model_Order::XML_PATH_EMAIL_GUEST_TEMPLATE,
                self::$_orderGuestEmailTemplate[$storeId]
            );
        }
    }

    public function getDefaultPoLimit()
    {
        return Mage::getStoreConfig('payment/purchaseorder/default_limit');
    }

    public function getAllowedCustomerGroups()
    {
        return Mage::getStoreConfig('payment/purchaseorder/customer_groups');
    }
}
