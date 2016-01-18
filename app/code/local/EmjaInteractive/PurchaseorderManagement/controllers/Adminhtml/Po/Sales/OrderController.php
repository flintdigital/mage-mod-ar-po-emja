<?php

class EmjaInteractive_PurchaseorderManagement_Adminhtml_Po_Sales_OrderController extends Mage_Adminhtml_Controller_Action
{
    public function printAction()
    {
//        die("this is order print");
        if ($orderId = $this->getRequest()->getParam('order_id')) {
            if ($order = Mage::getModel('sales/order')->load($orderId)) {
                $pdf = Mage::getModel('emjainteractive_purchaseordermanagement/sales_order_pdf')->getPdf(array($order));
                
                $incrementid = $order->getIncrementId();
                return $this->_prepareDownloadResponse('MethodSevenInvoice_#'.$incrementid.'.pdf', $pdf->render(), 'application/pdf');
//                return $this->_prepareDownloadResponse('order'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
            }
        } else {
            $this->_forward('noRoute');
        }
    }

    public function pdfordersAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        $pdf = null;
        if (!empty($orderIds)) {
            $orders = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToFilter('entity_id', $orderIds)
                ->load();

            if (count($orders)) {
                $flag = true;
                $pdf = Mage::getModel('emjainteractive_purchaseordermanagement/sales_order_pdf')->getPdf($orders);
            }

            if ($flag) {
                $firstOrderId = reset($orderIds);
                $order = Mage::getModel('sales/order')->load($firstOrderId);
                $incrementid = $order->getIncrementId();
                
                return $this->_prepareDownloadResponse('MethodSevenInvoice_#'.$incrementid.'.pdf', $pdf->render(), 'application/pdf');
                
//                return $this->_prepareDownloadResponse('order'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/sales_order/');
            }
        }

        $this->_redirect('*/sales_order/');
    }

    public function savePaymentAction()
    {
        $purchaseOrder = $this->getRequest()->getParam('purchaseorder', null);
        $orderId = $this->getRequest()->getParam('order_id');

        try {
            $order = Mage::getModel('sales/order')->load($orderId);
            if (!$order || !$order->getId()) {
                Mage::throwException('Could not find order for update.');
            }

            if (empty($purchaseOrder) || empty($purchaseOrder['number'])) {
                Mage::throwException('Purchase Order Number should not be empty.');
            }

            $payment = $order->getPayment();
            $originalPoNumber = $payment->getPoNumber();
            $payment->setPoNumber($purchaseOrder['number']);
            $order->setPOUpdated(true);
            $order->addStatusHistoryComment(sprintf(
                'Purchase Order Number changed by "%s" from "%s" to "%s".',
                Mage::getSingleton('admin/session')->getUser()->getUsername(),
                Mage::helper('core')->escapeHtml($originalPoNumber),
                Mage::helper('core')->escapeHtml($purchaseOrder['number'])
            ));

            /** @var Mage_Core_Model_Resource_Transaction $transaction */
            $transaction = Mage::getModel('core/resource_transaction');
            $transaction->addObject($order);
            $transaction->addObject($payment);
            $transaction->save();

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError('Could not save PurchaseOIrder number.');
        }

        return $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
    }

    /**
     * Process offline invoices on orders
     *
     * @return $this|Mage_Core_Controller_Varien_Action
     */
    public function processOfflineInvoicesAction()
    {
        $ids = $this->getRequest()->getParam('order_ids', array());
        try {
            if (empty($ids)) {
                Mage::throwException($this->__('Please select atleast one order.'));
            }
            /** @var Mage_Sales_Model_Resource_Order_Collection $orders */
            $orders = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToFilter('entity_id', $ids)
                ->load();

            /** @var Mage_Sales_Model_Order $order */
            foreach ($orders as $order) {
                try {
                    if ($order->canInvoice()) {
                        $invoice = $order->prepareInvoice();
                        if (!$invoice->getTotalQty()) {
                            Mage::throwException($this->__('Cannot create invoice without products.'));
                        }
                        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                        $invoice->register();
                        $invoice->addComment($this->__(
                            'Captured offline by <strong>%s</strong>', $this->_getAdminUserName()
                        ));
                        $invoice->getOrder()->addStatusHistoryComment(
                            $this->__(
                                'Invoice was captured offline by <strong>%s</strong>',
                                $this->_getAdminUserName()
                            )
                        );
                        $transaction = Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder());
                        $transaction->save();
                        $invoice->sendEmail()->save();
                        $this->_getSession()->addSuccess($this->__(
                            'Invoice "%s" for order "%s" created.', $invoice->getIncrementId(), $order->getIncrementId()
                        ));
                    } else {
                        $this->_getSession()->addError($this->__('Order "%s" cannot be invoiced.', $order->getIncrementId()));
                    }
                } catch (Mage_Core_Exception $e) {
                    $this->_getSession()->addError($this->__(
                        'Order "%s" cannot be invoiced. %s', $order->getIncrementId(), $e->getMessage()
                    ));
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_getSession()->addError($this->__('Order "%s" cannot be invoiced.', $order->getIncrementId()));
                }
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($this->__('Cannot invoice orders.'));
        }
        return $this->_redirect('*/sales_order');
    }

    protected function _getAdminUserName()
    {
        return Mage::getSingleton('admin/session')->getUser()->getUsername();
    }

    protected function _isAllowed()
    {
        $actionName = $this->getRequest()->getActionName();
        $actionName = strtolower($actionName);
        $isAllowed = true;
        switch ($actionName) {
            case 'processofflineinvoices':
                $isAllowed = Mage::getSingleton('admin/session')->isAllowed('purchaseorder/process_offline_invoices');
                break;
            default:
                $isAllowed = true;
                break;
        }
        return $isAllowed;
    }
}
