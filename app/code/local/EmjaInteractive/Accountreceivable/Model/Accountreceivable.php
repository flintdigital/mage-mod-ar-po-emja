<?php

class EmjaInteractive_Accountreceivable_Model_Accountreceivable extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('accountreceivable/accountreceivable');
    }
	
	public function getTransactionNote($increment_id)
    {
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$query = "SELECT * FROM " . $resource->getTableName('emja_ar_comments') . " WHERE increment_id = '" . $increment_id . "'";
		$result = $readConnection->fetchRow($query);

		if(is_array($result) and array_key_exists('comment', $result)) {
			return $result['comment'];
		}
		return '';
	}
	
	public function saveTransactionNote($increment_id, $notesText)
    {
		$resource = Mage::getSingleton('core/resource');
		$writeConnection = $resource->getConnection('core_write');

		if($this->getTransactionNote($increment_id) == '') {
			$query = "INSERT INTO " . $resource->getTableName('emja_ar_comments') . " (increment_id, comment) VALUES('" . $increment_id . "', '" . $notesText . "')";
			$writeConnection->query($query);
		} else {
			$query = 'UPDATE ' . $resource->getTableName('emja_ar_comments') . ' SET comment = "' . $notesText . '" WHERE increment_id = "' . $increment_id . '"';
			$writeConnection->query($query);
		}
	}
}