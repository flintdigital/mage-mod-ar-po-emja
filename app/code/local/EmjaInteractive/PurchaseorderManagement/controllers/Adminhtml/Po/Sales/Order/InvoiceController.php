<?php

require_once 'app/code/core/Mage/Adminhtml/controllers/Sales/Order/InvoiceController.php';

class EmjaInteractive_PurchaseorderManagement_Adminhtml_Po_Sales_Order_InvoiceController
    extends Mage_Adminhtml_Sales_Order_InvoiceController
{
    protected function _checkIfNoPartialInvoicing($invoice)
    {
        $qtys = $this->_getItemQtys();
        foreach($invoice->getOrder()->getAllVisibleItems() as $item) {
            if (!isset($qtys[$item->getItemId()])) {
                Mage::throwException('You must invoice all items. Partial capturing is disallowed here.');
            }

            if ($qtys[$item->getItemId()] < (int)$item->getQtyOrdered()) {
                Mage::throwException('You must invoice all items. Partial capturing is disallowed here.');
            }
        }
    }

    public function saveAction()
    {
        $data = $this->getRequest()->getPost('invoice');
        $orderId = $this->getRequest()->getParam('order_id');

        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            $invoice = $this->_initInvoice();

            if ($invoice) {

                if (!empty($data['capture_case'])) {
                    $invoice->setRequestedCaptureCase($data['capture_case']);
                }

                if (!empty($data['comment_text'])) {
                    $invoice->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );
                }

                $capturePayment = false;
                
                if (!empty($data['capture_case']) && ($data['capture_case'] == 'online')) {
                    $paymentInfo = $this->getRequest()->getPost('payment', array());
                    if (is_array($paymentInfo) && count($paymentInfo) && isset($paymentInfo['method'])) {

                        $this->_checkIfNoPartialInvoicing($invoice);

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
                    }
                }

                $invoice->register();

                if (!empty($data['send_email'])) {
                    $invoice->setEmailSent(true);
                }

                $invoice->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $invoice->getOrder()->setIsInProcess(true);


                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());


                $shipment = false;
                if (!empty($data['do_shipment']) || (int) $invoice->getOrder()->getForcedDoShipmentWithInvoice()) {
                    $shipment = $this->_prepareShipment($invoice);
                    if ($shipment) {
                        $shipment->setEmailSent($invoice->getEmailSent());
                        $transactionSave->addObject($shipment);
                    }
                }
                $transactionSave->save();

                if (!empty($data['do_shipment'])) {
                    $this->_getSession()->addSuccess($this->__('The invoice and shipment have been created.'));
                } else {
                    $this->_getSession()->addSuccess($this->__('The invoice has been created.'));
                }

                // send invoice/shipment emails
                $comment = '';
                if (isset($data['comment_customer_notify'])) {
                    $comment = $data['comment_text'];
                }
                try {
                    $invoice->sendEmail(!empty($data['send_email']), $comment);
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_getSession()->addError($this->__('Unable to send the invoice email.'));
                }
                if ($shipment) {
                    try {
                        $shipment->sendEmail(!empty($data['send_email']));
                    } catch (Exception $e) {
                        Mage::logException($e);
                        $this->_getSession()->addError($this->__('Unable to send the shipment email.'));
                    }
                }
                Mage::getSingleton('adminhtml/session')->getCommentText(true);
                $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
            } else {
                $this->_redirect('*/sales_order_invoice/new', array('order_id' => $orderId));
            }
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            //Mage::logException($e);
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Unable to save the invoice.'));
            Mage::logException($e);
        }
        $this->_redirect('*/sales_order_invoice/new', array('order_id' => $orderId));
    }

}