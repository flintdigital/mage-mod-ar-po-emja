<?php
class EmjaInteractive_PurchaseorderManagement_Block_Adminhtml_Sales_Order
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'emjainteractive_purchaseordermanagement';
        $this->_controller = 'adminhtml_sales_order';
        $this->_headerText = Mage::helper('sales')->__('Purchase Orders');
        $this->_addButtonLabel = Mage::helper('sales')->__('Create New Order');
        parent::__construct();
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/create')) {
            $this->_removeButton('add');
        }
    }

    /* (non-PHPdoc)
     * @see Mage_Adminhtml_Block_Widget_Grid_Container::getCreateUrl()
     */
    public function getCreateUrl()
    {
        return $this->getUrl('adminhtml/sales_order_create/start');
    }

}
