<?php

class EmjaInteractive_PurchaseorderManagement_Model_Adminhtml_Sales_Order_Grid_Observer
{
    public function beforeHtml($observer)
    {
        $block = $observer->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid) {
            if ($paymentMethodColumn = $block->getColumn('payment_method')) {
                $paymentMethodColumn->setData(
                    'options',
                    Mage::getSingleton('emjainteractive_purchaseordermanagement/adminhtml_system_config_source_order_payment_method')->getPaymentMethods()
                );
            }

            $block->getMassactionBlock()->addItem('pdforders_order', array(
                 'label'=> Mage::helper('sales')->__('Print PO Invoice'),
                 'url'  => $block->getUrl('*/po_sales_order/pdforders'),
            ));
        }
    }
}