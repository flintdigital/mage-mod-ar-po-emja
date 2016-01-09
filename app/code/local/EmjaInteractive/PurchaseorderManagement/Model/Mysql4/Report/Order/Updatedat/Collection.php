<?php
class EmjaInteractive_PurchaseorderManagement_Model_Mysql4_Report_Order_Updatedat_Collection
    extends Mage_Sales_Model_Mysql4_Report_Order_Updatedat_Collection
{

    public function __construct()
    {
        parent::_construct();
        $this->_selectedColumns['net_terms'] = 'net_terms';
    }

}
