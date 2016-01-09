<?php

class EmjaInteractive_PurchaseorderManagement_Adminhtml_Po_Sales_OrderController extends Mage_Adminhtml_Controller_Action
{
    public function printAction()
    {
        Mage::register('emja_printing', true);
        if ($orderId = $this->getRequest()->getParam('order_id')) {
            if ($order = Mage::getModel('sales/order')->load($orderId)) {
                $pdf = Mage::getModel('emjainteractive_purchaseordermanagement/sales_order_pdf')->getPdf(array($order));
                $incrementid = $order->getIncrementId();
                return $this->_prepareDownloadResponse('MethodSevenInvoice_#'.$incrementid.'.pdf', $pdf->render(), 'application/pdf');
//                 return $this->_prepareDownloadResponse('order'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
            }
        }
        else {
            $this->_forward('noRoute');
        }
    }

    public function pdfordersAction()
    {
        Mage::register('emja_printing', true);
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
//                 return $this->_prepareDownloadResponse('order'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/sales_order/');
            }
        }
        $this->_redirect('*/sales_order/');
    }
}
