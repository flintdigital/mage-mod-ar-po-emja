<?php

require_once '../abstract.php';

class PoUpgrade303 extends Mage_Shell_Abstract
{
    /** @var Varien_Db_Adapter_Interface  */
    protected $_db = null;

    protected $_poLimitId = null;
    protected $_poCreditId = null;

    /**
     * Additional initialization
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_db = Mage::getModel('core/resource')->getConnection('core_write');
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     */
    protected function _moveAttributesData($customer)
    {
        $attributes = array(
            'po_limit' => $this->_poLimitId,
            'po_credit' => $this->_poCreditId
        );
        foreach ($attributes as $attributeCode => $attributeId) {
            try {
                $select = $this->_db->select()
                    ->from($customer->getResource()->getTable('customer_entity_int'), array('value'))
                    ->where('attribute_id = ?', $attributeId)
                    ->where('entity_id = ?', $customer->getId());
                $value = $this->_db->fetchOne($select);
                if ((int)$value > 0) {
                    $this->_db->insert(
                        $customer->getResource()->getTable('customer_entity_decimal'),
                        array(
                            'entity_type_id' => $customer->getEntityTypeId(),
                            'attribute_id' => $attributeId,
                            'entity_id' => $customer->getId(),
                            'value' => (int)$value
                        )
                    );
                }
            } catch (Exception $e) {
            }
            try {
                $this->_db->delete(
                    $customer->getResource()->getTable('customer_entity_int'),
                    'entity_type_id = ' . $customer->getEntityTypeId() . ' AND attribute_id = ' . $attributeId . ' AND '
                    . 'entity_id = ' . $customer->getId()
                );
            } catch (Exception $e) {
            }
        }
    }

    public function run()
    {
        $customerModel = Mage::getModel('customer/customer');

        $this->_poLimitId = $customerModel->getResource()->getAttribute('po_limit')->getId();
        $this->_poCreditId = $customerModel->getResource()->getAttribute('po_credit')->getId();

        $page = 1;
        $collection = $customerModel->getCollection();
        $collection->setPageSize(100);
        $pages = $collection->getLastPageNumber();
        do {
            $collection->setCurPage($page);
            $collection->load();
            foreach ($collection as $customer) {
                $this->_moveAttributesData($customer);
            }
            $page++;
            $collection->clear();
        } while ($page <= $pages);

        return $this;
    }
}

$shell = new PoUpgrade303();
$shell->run();