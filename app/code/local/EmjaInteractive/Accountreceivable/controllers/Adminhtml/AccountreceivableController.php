<?php

class EmjaInteractive_Accountreceivable_Adminhtml_AccountreceivableController extends Mage_Adminhtml_Controller_Report_Abstract
{
	public function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('report/accountreceivable')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Account Receivables'), Mage::helper('adminhtml')->__('Account Receivables'));
		
		return $this;
	}
	
	public function indexAction() {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Account Receivables'));
		
		$this->_initAction()
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Account Receivables'), Mage::helper('adminhtml')->__('Account Receivables'))
            ->_addContent($this->getLayout()->createBlock('accountreceivable/adminhtml_accountreceivable'))
            ->renderLayout();
    }
	
    public function getNoteTextAction()
    {
		$increment_id = $this->getRequest()->getParam('increment_id');
		$noteText = Mage::helper('accountreceivable')->getTransactionNote($increment_id);
		
		$result = array('transaction_id' => $increment_id,
						'note_text' => $noteText
					);
		
		echo json_encode($result);exit;
	}
	
	public function saveNoteTextAction()
    {
		$increment_id = $this->getRequest()->getParam('increment_id');
		$notesText = $this->getRequest()->getParam('notes');
		
		try {
			Mage::getModel('accountreceivable/accountreceivable')->saveTransactionNote($increment_id, $notesText);
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('accountreceivable')->__('Note was successfully saved'));
		} catch(Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('accountreceivable')->__('An error occured. Note was not saved'));
		}
	}
	
	public function exportCsvAction()
    {
        $fileName = 'accountreceivable.csv';
		$content  = $this->getLayout()->createBlock('accountreceivable/adminhtml_accountreceivable_grid')->getCsv();

		$this->_sendUploadResponse($fileName, $content);
    }
	
    public function exportXmlAction()
    {
		$fileName = 'accountreceivable.xml';
		$content  = $this->getLayout()->createBlock('accountreceivable/adminhtml_accountreceivable_grid')->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }
	
    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}