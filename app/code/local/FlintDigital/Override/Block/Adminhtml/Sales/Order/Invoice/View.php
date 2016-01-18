<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml invoice create
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class FlintDigital_Override_Block_Adminhtml_Sales_Order_Invoice_View extends Mage_Adminhtml_Block_Sales_Order_Invoice_View
{
    public function __construct()
    {
        $this->_objectId    = 'invoice_id';
        $this->_controller  = 'sales_order_invoice';
        $this->_mode        = 'view';
        $this->_session = Mage::getSingleton('admin/session');

        parent::__construct();

        $this->_removeButton('save');
        $this->_removeButton('reset');
        $this->_removeButton('delete');
        
        if ($this->_isAllowedAction('cancel') && $this->getInvoice()->canCancel()) {
            $this->_addButton('cancel', array(
                'label'     => Mage::helper('sales')->__('Cancel'),
                'class'     => 'delete',
                'onclick'   => 'setLocation(\''.$this->getCancelUrl().'\')'
                )
            );
        }

        if ($this->_isAllowedAction('emails')) {
            $this->addButton('send_notification', array(
                'label'     => Mage::helper('sales')->__('Send Email'),
                'onclick'   => 'confirmSetLocation(\''
                . Mage::helper('sales')->__('Are you sure you want to send Invoice email to customer?')
                . '\', \'' . $this->getEmailUrl() . '\')'
            ));
        }

        $orderPayment = $this->getInvoice()->getOrder()->getPayment();

        if ($this->_isAllowedAction('creditmemo') && $this->getInvoice()->getOrder()->canCreditmemo()) {
            if (($orderPayment->canRefundPartialPerInvoice()
                && $this->getInvoice()->canRefund()
                && $orderPayment->getAmountPaid() > $orderPayment->getAmountRefunded())
                || ($orderPayment->canRefund() && !$this->getInvoice()->getIsUsedForRefund())) {
                $this->_addButton('capture', array( // capture?
                    'label'     => Mage::helper('sales')->__('Credit Memo'),
                    'class'     => 'go',
                    'onclick'   => 'setLocation(\''.$this->getCreditMemoUrl().'\')'
                    )
                );
            }
        }

        if ($this->_isAllowedAction('capture') && $this->getInvoice()->canCapture()) {
            $this->_addButton('capture', array(
                'label'     => Mage::helper('sales')->__('Capture'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getCaptureUrl().'\')'
                )
            );
        }

        if ($this->getInvoice()->canVoid()) {
            $this->_addButton('void', array(
                'label'     => Mage::helper('sales')->__('Void'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getVoidUrl().'\')'
                )
            );
        }

        if ($this->getInvoice()->getId()) {
            $this->_addButton('print', array(
                'label'     => Mage::helper('sales')->__('Print'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getPrintUrl().'\')'
                )
            );
        }

        if ($this->getInvoice()->getId()) {
            $this->_addButton('print_comments', array(
                'label'     => Mage::helper('sales')->__('Print + Comments'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getPrintUrl().'c/1\')'
                )
            );
        }
    }
}
