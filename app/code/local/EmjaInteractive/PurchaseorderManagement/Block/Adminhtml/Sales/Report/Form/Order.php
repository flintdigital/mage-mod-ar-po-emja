<?php
class EmjaInteractive_PurchaseorderManagement_Block_Adminhtml_Sales_Report_Form_Order
    extends Mage_Adminhtml_Block_Report_Filter_Form
{
    /* (non-PHPdoc)
     * @see Mage_Adminhtml_Block_Report_Filter_Form::_prepareForm()
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $htmlIdPrefix = $form->getHtmlIdPrefix();
        /** @var Varien_Data_Form_Element_Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');
        if (is_object($fieldset) && $fieldset instanceof Varien_Data_Form_Element_Fieldset) {
            $fieldset->addField('order_statuses', 'hidden', array(
                'name'      => 'order_statuses',
                'value'     => EmjaInteractive_PurchaseorderManagement_Model_Sales_Order_Status::STATUS_PURCHASEORDER_PENDING_PAYMENT,
            ), 'show_order_statuses');
            $fieldset->addField('show_actual_columns', 'select', array(
                'name'       => 'show_actual_columns',
                'options'    => array(
                    '1' => Mage::helper('reports')->__('Yes'),
                    '0' => Mage::helper('reports')->__('No')
                ),
                'label'      => Mage::helper('reports')->__('Show Actual Values'),
            ));
        }
        return $this;
    }

}
