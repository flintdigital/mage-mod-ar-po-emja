<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer admin controller
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
 require_once 'Mage/Adminhtml/controllers/CustomerController.php';
 
 //DEPRECATED, this is not used anymore. See FlintDigital_DealerLocator Plugin
 
class Flint_Override_CustomerController extends Mage_Adminhtml_CustomerController
{

    
/*******GEOCODE SAVE : Customer address Geocode save in database*******/
 public function geocodesaveAction()
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
	$query="UPDATE m7_customer_address_entity SET geocode='".$_GET['val']."' WHERE entity_id='".$_GET['id']."'";
	$result=$write->query($query);
    }
    
    

    /**
     * Save customer action
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            $redirectBack   = $this->getRequest()->getParam('back', false);
            $this->_initCustomer('customer_id');

            /* @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::registry('current_customer');

            /* @var $customerForm Mage_Customer_Model_Form */
            $customerForm = Mage::getModel('customer/form');
            $customerForm->setEntity($customer)
                ->setFormCode('adminhtml_customer')
                ->ignoreInvisible(false)
            ;

            $formData   = $customerForm->extractData($this->getRequest(), 'account');
            $errors     = $customerForm->validateData($formData);
            if ($errors !== true) {
                foreach ($errors as $error) {
                    $this->_getSession()->addError($error);
                }
                $this->_getSession()->setCustomerData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/customer/edit', array('id' => $customer->getId())));
                return;
            }

            $customerForm->compactData($formData);

            // unset template data
            if (isset($data['address']['_template_'])) {
                unset($data['address']['_template_']);
            }

            $modifiedAddresses = array();
            if (!empty($data['address'])) {
                /* @var $addressForm Mage_Customer_Model_Form */
                $addressForm = Mage::getModel('customer/form');
                $addressForm->setFormCode('adminhtml_customer_address')->ignoreInvisible(false);

                foreach ($data['address'] as $index=>$dataAddress) {
                	//This line will ignore the address to be delted in the $modifiedAddresses array, making it available to delete
                	if(count($dataAddress) === 1 && array_key_exists('_deleted', $dataAddress))
                		continue;
                	
                    $address = $customer->getAddressItemById($index);
                    if (!$address) {
                        $address   = Mage::getModel('customer/address');
                    }

                    $requestScope = sprintf('address/%s', $index);
                    $formData = $addressForm->setEntity($address)
                        ->extractData($this->getRequest(), $requestScope);
                    $errors   = $addressForm->validateData($formData);
                    if ($errors !== true) {
                        foreach ($errors as $error) {
                            $this->_getSession()->addError($error);
                        }
                        $this->_getSession()->setCustomerData($data);
                        $this->getResponse()->setRedirect($this->getUrl('*/customer/edit', array(
                            'id' => $customer->getId())
                        ));
                        return;
                    }

                    $addressForm->compactData($formData);

                    // Set post_index for detect default billing and shipping addresses
                    $address->setPostIndex($index);

                    if ($address->getId()) {
                        $modifiedAddresses[] = $address->getId();
                    } else {
                        $customer->addAddress($address);
                    }
                }
            }

            // default billing and shipping
            if (isset($data['account']['default_billing'])) {
                $customer->setData('default_billing', $data['account']['default_billing']);
            }
            if (isset($data['account']['default_shipping'])) {
                $customer->setData('default_shipping', $data['account']['default_shipping']);
            }
            if (isset($data['account']['confirmation'])) {
                $customer->setData('confirmation', $data['account']['confirmation']);
            }

            // not modified customer addresses mark for delete
            foreach ($customer->getAddressesCollection() as $customerAddress) {
                if ($customerAddress->getId() && !in_array($customerAddress->getId(), $modifiedAddresses)) {
                    $customerAddress->setData('_deleted', true);
                }
            }

            if (isset($data['subscription'])) {
                $customer->setIsSubscribed(true);
            } else {
                $customer->setIsSubscribed(false);
            }

            if (isset($data['account']['sendemail_store_id'])) {
                $customer->setSendemailStoreId($data['account']['sendemail_store_id']);
            }

            $isNewCustomer = $customer->isObjectNew();
            try {
                $sendPassToEmail = false;
                // force new customer active
                if ($isNewCustomer) {
                    $customer->setPassword($data['account']['password']);
                    $customer->setForceConfirmed(true);
                    if ($customer->getPassword() == 'auto') {
                        $sendPassToEmail = true;
                        $customer->setPassword($customer->generatePassword());
                    }
                }

                Mage::dispatchEvent('adminhtml_customer_prepare_save', array(
                    'customer'  => $customer,
                    'request'   => $this->getRequest()
                ));

                $customer->save();

                // send welcome email
                if ($customer->getWebsiteId() && (!empty($data['account']['sendemail']) || $sendPassToEmail)) {
                    $storeId = $customer->getSendemailStoreId();
                    if ($isNewCustomer) {
                        $customer->sendNewAccountEmail('registered', '', $storeId);
                    }
                    // confirm not confirmed customer
                    else if ((!$customer->getConfirmation())) {
                        $customer->sendNewAccountEmail('confirmed', '', $storeId);
                    }
                }


/**************Dealer Locator code starts here**********************/
//update "View on Dealer Locator" attribute value
				$write = Mage::getSingleton('core/resource')->getConnection('core_write');
				$addresscount=count($customer->getAddressesCollection());
				
				foreach($customer->getAddressesCollection() as $address){
					//Added so we don't parse recently deleted addresses: Error on Customer Save when deleting an address FIX
					if(!in_array($address->getId(), $modifiedAddresses))
						continue;
					
					$query2="SELECT * FROM m7_customer_address_entity_varchar WHERE entity_type_id ='2' AND attribute_id ='213' AND entity_id ='".$address->getId()."'";
					$data2=$write->fetchAll($query2);
					$count1=count($data2);
					if($count1){
						$query3="UPDATE m7_customer_address_entity_varchar SET value='".$data['address'][$address->getId()]['dealer_locator']."' WHERE entity_type_id='2' AND attribute_id='213' AND entity_id='".$address->getId()."'";
					}
					
					else{
						if($data['address'][$address->getId()]){
							$query3="INSERT INTO m7_customer_address_entity_varchar (value_id, entity_type_id, attribute_id, entity_id, value) VALUES (NULL, '2', '213', '".$address->getId()."', '".$data['address'][$address->getId()]['dealer_locator']."')";
						}
						else {
							$query3="INSERT INTO m7_customer_address_entity_varchar (value_id, entity_type_id, attribute_id, entity_id, value) VALUES (NULL, '2', '213', '".$address->getId()."', '".$data['address']['_item'.$addresscount]['dealer_locator']."')";
						}
					}
	            	$write->query($query3);
				
	            	$customer_address=Mage::getModel('customer/address')->load($address->getId());
					if($data['address'][$address->getId()]){
						$customer_address->setStreet($data['address'][$address->getId()]['street'][0]." ".$data['address'][$address->getId()]['street'][1]);
					}
					
					else {
						$customer_address->setStreet($data['address']['_item'.$addresscount]['street'][0]." ".$data['address']['_item'.$addresscount]['street'][1]);
					}
					
					$customer_address->save();
				}//foreach
				
/**************Dealer Locator code end here**********************/		
		
                if (!empty($data['account']['new_password'])) {
                    $newPassword = $data['account']['new_password'];
                    if ($newPassword == 'auto') {
                        $newPassword = $customer->generatePassword();
                    }
                    $customer->changePassword($newPassword);
                    $customer->sendPasswordReminderEmail();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('The customer has been saved.')
                );
                Mage::dispatchEvent('adminhtml_customer_save_after', array(
                    'customer'  => $customer,
                    'request'   => $this->getRequest()
                ));

                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array(
                        'id'    => $customer->getId(),
                        '_current'=>true
                    ));
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setCustomerData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/customer/edit', array('id' => $customer->getId())));
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('adminhtml')->__('An error occurred while saving the customer. '.$e->getMessage().' <br />msg:<br />'.$dbg));
                $this->_getSession()->setCustomerData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/customer/edit', array('id'=>$customer->getId())));
                return;
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/customer'));
    }
}
