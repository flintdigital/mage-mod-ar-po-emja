<?php

class EmjaInteractive_PurchaseorderManagement_Model_Sales_Order_Shipment_Observer
{

    /**
     * Updates status of PO orders when shipped
     *
     * @param Varien_Event_Observer $observer
     * @return EmjaInteractive_PurchaseorderManagement_Model_Sales_Order_Shipment_Observer
     */
    public function setStatus($observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        if ($shipment instanceof Mage_Sales_Model_Order_Shipment) {
            $order = $shipment->getOrder();
            $payment = $order->getPayment();
            if ($payment && ($payment->getMethod() == 'purchaseorder')) {
                $order->setState(
                    Mage_Sales_Model_Order::STATE_PROCESSING,
                    EmjaInteractive_PurchaseorderManagement_Model_Sales_Order_Status::STATUS_PURCHASEORDER_PENDING_PAYMENT
                )
                ->save();
            }
        }
        return $this;
    }
}
