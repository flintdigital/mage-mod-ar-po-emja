<?php

require_once 'app/code/core/Mage/Adminhtml/controllers/Sales/Order/CreditmemoController.php';

class EmjaInteractive_PurchaseorderManagement_Adminhtml_Po_Sales_Order_CreditmemoController
    extends Mage_Adminhtml_Sales_Order_CreditmemoController
{
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('creditmemo');
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            $creditmemo = $this->_initCreditmemo();
            if ($creditmemo) {
                if (($creditmemo->getGrandTotal() <=0) && (!$creditmemo->getAllowZeroGrandTotal())) {
                    Mage::throwException(
                        $this->__('Credit memo\'s total must be positive.')
                    );
                }

                $comment = '';
                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );
                    if (isset($data['comment_customer_notify'])) {
                        $comment = $data['comment_text'];
                    }
                }

                if (isset($data['do_refund'])) {
                    $creditmemo->setRefundRequested(true);
                }
                if (isset($data['do_offline'])) {
                    $creditmemo->setOfflineRequested((bool)(int)$data['do_offline']);
                }

                $capturePayment = Mage::helper('emjainteractive_purchaseordermanagement/payment')->getCapturePayment(
                    $creditmemo->getOrder()
                );
                if ($capturePayment->getId()) {
                    /**
                     * Order with capture payment
                     */
                    $captureCreditmemo = clone $creditmemo;
                    $this->_refundCapturedMoney($captureCreditmemo);
                    
                }

                $creditmemo->register();



                if (!empty($data['send_email'])) {
                    $creditmemo->setEmailSent(true);
                }

                $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $this->_saveCreditmemo($creditmemo);
                $creditmemo->sendEmail(!empty($data['send_email']), $comment);
                $this->_getSession()->addSuccess($this->__('The credit memo has been created.'));
                Mage::getSingleton('adminhtml/session')->getCommentText(true);
                $this->_redirect('*/sales_order/view', array('order_id' => $creditmemo->getOrderId()));
                return;
            } else {
                $this->_forward('noRoute');
                return;
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->setFormData($data);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($this->__('Cannot save the credit memo.'));
        }
        $this->_redirect('*/sales_order_creditmemo/new', array('_current' => true));
    }

    protected function _refundCapturedMoney($captureCreditmemo)
    {
        $baseOrderRefund = Mage::app()->getStore()->roundPrice(
            $captureCreditmemo->getOrder()->getBaseTotalRefunded()+$captureCreditmemo->getBaseGrandTotal()
        );

        if ($baseOrderRefund > Mage::app()->getStore()->roundPrice($captureCreditmemo->getOrder()->getBaseTotalPaid())) {

            $baseAvailableRefund = $captureCreditmemo->getOrder()->getBaseTotalPaid()- $captureCreditmemo->getOrder()->getBaseTotalRefunded();

            Mage::throwException(
                Mage::helper('sales')->__('Maximum amount available to refund is %s',
                    $this->getOrder()->formatBasePrice($baseAvailableRefund)
                )
            );
        }

        $capturePayment = Mage::helper('emjainteractive_purchaseordermanagement/payment')->getCapturePayment(
            $captureCreditmemo->getOrder()
        );


        $capturePayment->refund($captureCreditmemo);
    }
}