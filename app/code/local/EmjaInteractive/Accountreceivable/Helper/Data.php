<?php

class EmjaInteractive_Accountreceivable_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getTransactionNote($increment_id)
    {
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$query = 'SELECT * FROM ' . $resource->getTableName('emja_ar_comments') . ' WHERE increment_id = "' . $increment_id . '"';
		$result = $readConnection->fetchRow($query);

		if(is_array($result) and array_key_exists('comment', $result))	return $result['comment'];
		
		return '';
	}
}