<?php

class EmjaInteractive_PurchaseorderManagement_Model_Observer
{
    public function coreEmailTemplateSendBefore(Varien_Event_Observer $observer)
    {
        /** @var Mage_Core_Model_Email_Template $emailTemplate */
        $emailTemplate = $observer->getEvent()->getObject();

        $mail = $emailTemplate->getMail();

        $attachment = Mage::registry('purchase-order-email-attachment');
        if (!empty($attachment)) {
            $attachmentFile = $mail->createAttachment($attachment['fileContents']);
            $attachmentFile->type = 'application/pdf';
            $attachmentFile->filename = $attachment['fileName'];
        }
    }

    public function disablePoTermsEdit(Varien_Event_Observer $observer)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('purchaseorder/edit_terms')) {
            return $this;
        }
        /** @var Mage_Adminhtml_Block_Customer_Edit_Tab_Account $block */
        $block = $observer->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Customer_Edit_Tab_Account) {
            $elements = array('net_terms', 'po_limit', 'po_credit');
            foreach ($elements as $elementId) {
                if ($block->getForm()->getElement($elementId)) {
                    $block->getForm()->getElement($elementId)->setDisabled('disabled');
                }
            }
        }
    }
}
