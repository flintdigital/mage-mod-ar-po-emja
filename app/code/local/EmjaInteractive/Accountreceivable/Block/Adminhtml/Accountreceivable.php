<?php
class EmjaInteractive_Accountreceivable_Block_Adminhtml_Accountreceivable extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	protected function _prepareLayout()
    {
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->addCss('emjainteractive/accountreceivable/accountreceivable.css');
        }
        
        return parent::_prepareLayout();
    }
	
	public function __construct()
	{
		$this->_controller = 'adminhtml_accountreceivable';
		$this->_blockGroup = 'accountreceivable';
		$this->_headerText = Mage::helper('accountreceivable')->__('Account Receivable Report');
		parent::__construct();
		$this->setTemplate('emjainteractive/accountreceivable/grid.phtml');
		$this->_removeButton('add');
		$this->_addButton('show_report', array(
            'label'     => Mage::helper('core')->__('Show Report'),
			'onclick'   => '$(\'ar_report_form\').submit()',
        ));
	}
	
	public function getAllOrderCollection()
    {
        return Mage::getResourceModel('sales/order_grid_collection')
				->addAttributeToFilter('payment_method', 'purchaseorder')
				->addAttributeToSort('entity_id', 'DESC');
    }
	
	public function getOrderCollection($from, $to)
    {
        $collection = Mage::getResourceModel('sales/order_grid_collection')
				->addAttributeToFilter('payment_method', 'purchaseorder')
				->addAttributeToFilter('status', array('nin' => array('complete', 'canceled')));
		
		if($from != NULL)
			$collection->addAttributeToFilter('created_at', array('from' => $from));
		
		if($to != NULL)
			$collection->addAttributeToFilter('created_at', array('to' => $to));
		
		$collection->addAttributeToSort('entity_id', 'DESC');
		
		return $collection;
    }
	
	public function getCreditMemoCollection($from, $to)
    {
        $collection = Mage::getResourceModel('sales/order_creditmemo_grid_collection');
		
		if($from != NULL)
			$collection->addAttributeToFilter('created_at', array('from' => $from));
		
		if($to != NULL)
			$collection->addAttributeToFilter('created_at', array('to' => $to));
		
		$collection->addAttributeToSort('entity_id', 'DESC');
		
		return $collection;
    }
	
	public function getInvoiceCollection($from, $to)
    {
        $collection = Mage::getResourceModel('sales/order_invoice_grid_collection');
		
		if($from != NULL)
			$collection->addAttributeToFilter('created_at', array('from' => $from));
		
		if($to != NULL)
			$collection->addAttributeToFilter('created_at', array('to' => $to));
		
		$collection->addAttributeToSort('entity_id', 'DESC');
		
		return $collection;
	}
	
	public function getTransactionNote($increment_id)
    {
		return Mage::helper('accountreceivable')->getTransactionNote($increment_id);
	}
}