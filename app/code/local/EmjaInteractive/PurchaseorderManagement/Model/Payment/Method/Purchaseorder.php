<?php
class EmjaInteractive_PurchaseorderManagement_Model_Payment_Method_Purchaseorder extends Mage_Payment_Model_Method_Purchaseorder
{
	public function isAvailable($quote = null)
	{
		$isAvailable = parent::isAvailable($quote);
		
		Mage::getSingleton('core/session', array('name' => 'adminhtml'));
		if (Mage::getSingleton('admin/session')->isLoggedIn()) {
			return $isAvailable;
		}
		
		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customer	= Mage::getSingleton('customer/session')->getCustomer();
			$poLimit	= $customer->getPoLimit();
			$cartTotal	= Mage::getModel('checkout/cart')->getQuote()->getGrandTotal();
			
			if (!$poLimit) {
				$isAvailable = false;
			} elseif($poLimit < $cartTotal) {
				$isAvailable = false;
			}
		} else {
			$isAvailable = false;
		}
		
		return $isAvailable;
	}
}