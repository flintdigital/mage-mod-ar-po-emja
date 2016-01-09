<?php
class EmjaInteractive_PurchaseorderManagement_Model_Adminhtml_Sales_Order_View_Observer
{

    /**
     * Enter description here ...
     * @param unknown_type $observer
     * @return EmjaInteractive_PurchaseorderManagement_Model_Adminhtml_Sales_Order_View_Observer
     */
    public function prepareButtons($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {

            $order = $block->getOrder();
            $payment = $order->getPayment();
            if ($payment && ($payment->getMethod() == 'purchaseorder') && $order->canShip()) {
                $block->removeButton('order_invoice');
            }

            $block->addButton('print', array(
                'label'     => Mage::helper('sales')->__('Print'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.
                        Mage::getModel('adminhtml/url')->getUrl(
                            'adminhtml/po_sales_order/print',
                            array('order_id' => $order->getId())
                        )
                    .'\')'
                )
            );
            
        }
        return $this;
    }

}
