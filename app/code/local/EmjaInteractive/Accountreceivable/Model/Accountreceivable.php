<?php

class EmjaInteractive_Accountreceivable_Model_Accountreceivable extends Mage_Core_Model_Abstract
{
    const XML_PATH_EMAIL_IDENTITY                           = 'sales_email/order/identity';
    const XML_PATH_PO_INVOICE_NOTIFICATION_EMAIL_TEMPLATE   = 'accountreceivable/po_invoice_notification';

    public function _construct()
    {
        parent::_construct();
        $this->_init('accountreceivable/accountreceivable');
    }

    public function getTransactionNote($increment_id)
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $query = "SELECT * FROM " . $resource->getTableName('emja_ar_comments') . " WHERE increment_id = '" . $increment_id . "'";
        $result = $readConnection->fetchRow($query);

        if(is_array($result) and array_key_exists('comment', $result)) {
            return $result['comment'];
        }
        return '';
    }

    public function saveTransactionNote($increment_id, $notesText)
    {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');

        if($this->getTransactionNote($increment_id) == '') {
            $query = "INSERT INTO " . $resource->getTableName('emja_ar_comments') . " (increment_id, comment) VALUES('" . $increment_id . "', '" . $notesText . "')";
            $writeConnection->query($query);
        } else {
            $query = 'UPDATE ' . $resource->getTableName('emja_ar_comments') . ' SET comment = "' . $notesText . '" WHERE increment_id = "' . $increment_id . '"';
            $writeConnection->query($query);
        }
    }

    public function resendInvoiceEmail($orderId)
    {
        $order  = Mage::getModel('sales/order')->load($orderId);
        $store  = Mage::app()->getStore();
        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo  = Mage::getModel('core/email_info');
        $templateId = Mage::getStoreConfig(self::XML_PATH_PO_INVOICE_NOTIFICATION_EMAIL_TEMPLATE, $store->getId());
        $emailInfo->addTo($order->getCustomerEmail(), $order->getCustomerName());
        $mailer->addEmailInfo($emailInfo);

        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $store->getId()));
        $mailer->setStoreId($store->getId());
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'store' => $store,
                'order' => $order
            )
        );

        $pdf = Mage::getModel('emjainteractive_purchaseordermanagement/sales_order_pdf')->getPoInvoiceForAttachment($orderId);
        if ($pdf) {
            $mailer->addAttachment($pdf->render(), 'order'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf');
        }

        $mailer->send();
        return $this;
    }
}
