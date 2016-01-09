<?php
class EmjaInteractive_PurchaseorderManagement_Block_Adminhtml_Report_Sales_Sales_Grid
    extends Mage_Adminhtml_Block_Report_Sales_Sales_Grid
{

    /* (non-PHPdoc)
     * @see Mage_Adminhtml_Block_Report_Sales_Sales_Grid::_prepareColumns()
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->addColumn('net_terms', array(
            'header'        => Mage::helper('sales')->__('Net Terms'),
            'type'          => 'text',
            'index'         => 'net_terms',
            'sortable'      => false
        ));
        return $this;
    }

}
