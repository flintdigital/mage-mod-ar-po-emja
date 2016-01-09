<?php
require_once 'app/code/core/Mage/Adminhtml/controllers/Report/SalesController.php';

class EmjaInteractive_PurchaseorderManagement_Adminhtml_Po_Report_SalesController
    extends Mage_Adminhtml_Report_SalesController
{

    protected function _showLastExecutionTime($flagCode, $refreshCode)
    {
        $flag = Mage::getModel('reports/flag')->setReportFlagCode($flagCode)->loadSelf();
        $updatedAt = ($flag->hasData())
            ? Mage::app()->getLocale()->storeDate(
                0, new Zend_Date($flag->getLastUpdate(), Varien_Date::DATETIME_INTERNAL_FORMAT), true
            )
            : 'undefined';

        $refreshStatsLink = $this->getUrl('*/report_sales/refreshstatistics');
        $directRefreshLink = $this->getUrl('*/report_sales/refreshRecent', array('code' => $refreshCode));

        Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('adminhtml')->__('Last updated: %s. To refresh last day\'s <a href="%s">statistics</a>, click <a href="%s">here</a>.', $updatedAt, $refreshStatsLink, $directRefreshLink));
        return $this;
    }
    

    /* (non-PHPdoc)
     * @see Mage_Adminhtml_Report_SalesController::salesAction()
     */
    public function salesAction()
    {
        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('Sales'));

        $this->_showLastExecutionTime(Mage_Reports_Model_Flag::REPORT_ORDER_FLAG_CODE, 'sales');

        $this->_initAction()
            ->_setActiveMenu('report/sales/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Sales Report'), Mage::helper('adminhtml')->__('Sales Report'));

        $gridBlock = $this->getLayout()->getBlock('adminhtml_report_sales_sales.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }

    /* (non-PHPdoc)
     * @see Mage_Adminhtml_Report_SalesController::refreshRecentAction()
     */
    public function refreshRecentAction()
    {
        return $this->_forward('refreshRecent', 'report_statistics', 'admin');
    }

    /* (non-PHPdoc)
     * @see Mage_Adminhtml_Report_SalesController::exportSalesCsvAction()
     */
    public function exportSalesCsvAction()
    {
        $fileName   = 'sales.csv';
        $grid       = $this->getLayout()->createBlock('emjainteractive_purchaseordermanagement/adminhtml_report_sales_sales_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /* (non-PHPdoc)
     * @see Mage_Adminhtml_Report_SalesController::exportSalesExcelAction()
     */
    public function exportSalesExcelAction()
    {
        $fileName   = 'sales.xml';
        $grid       = $this->getLayout()->createBlock('emjainteractive_purchaseordermanagement/adminhtml_report_sales_sales_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    /* (non-PHPdoc)
     * @see Mage_Adminhtml_Report_SalesController::refreshStatisticsAction()
     */
    public function refreshStatisticsAction()
    {
        return $this->_forward('index', 'report_statistics', 'admin');
    }

    /* (non-PHPdoc)
     * @see Mage_Adminhtml_Report_SalesController::_isAllowed()
     */
    protected function _isAllowed()
    {
        return true;
    }
}
