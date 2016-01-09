<?php
class EmjaInteractive_PurchaseorderManagement_Block_Adminhtml_Report_Sales_Sales
    extends Mage_Adminhtml_Block_Report_Sales_Sales
{

    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'emjainteractive_purchaseordermanagement';
        $this->_controller = 'adminhtml_report_sales_sales';
        $this->_headerText = Mage::helper('reports')->__('Purchase Ordered Report');
    }

}
