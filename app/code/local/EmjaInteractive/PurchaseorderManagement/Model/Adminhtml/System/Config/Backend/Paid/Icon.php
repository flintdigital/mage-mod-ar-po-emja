<?php

class EmjaInteractive_PurchaseorderManagement_Model_Adminhtml_System_Config_Backend_Paid_Icon
    extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value) && isset($value['delete']) && $value['delete']) {
            $this->setValue('');
        }
        return parent::_beforeSave();
    }

    public function _afterSave()
    {
        if (empty($_FILES['groups']['tmp_name']['purchaseorder']['fields']['paid_icon']['value'])) {
            return $this;
        }

        try {
            $_FILES['paid_icon'] = array(
                'name' => $_FILES['groups']['name']['purchaseorder']['fields']['paid_icon']['value'],
                'type' => $_FILES['groups']['type']['purchaseorder']['fields']['paid_icon']['value'],
                'tmp_name' => $_FILES['groups']['tmp_name']['purchaseorder']['fields']['paid_icon']['value'],
                'error' => $_FILES['groups']['error']['purchaseorder']['fields']['paid_icon']['value'],
                'size' => $_FILES['groups']['size']['purchaseorder']['fields']['paid_icon']['value'],
            );

            $path = Mage::helper('emjainteractive_purchaseordermanagement')->getIconMediaPath();

            $uploader = new Mage_Core_Model_File_Uploader('paid_icon');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(false);
            $result = $uploader->save($path);
        } catch (Exception $e) {
            Mage::logException($e);
            throw new Exception($e);
        }
    }
}