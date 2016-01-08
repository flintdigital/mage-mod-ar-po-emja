<?php

class EmjaInteractive_PurchaseorderManagement_Model_Email_Template_Mailer extends Mage_Core_Model_Email_Template_Mailer
{
	protected $_attachedFile = array();

	public function addAttachment($fileContents, $fileName)
	{
		$tmp = array();
		$tmp['fileContents'] = $fileContents;
		$tmp['fileName'] = $fileName;

		$this->_attachedFile = $tmp;

		return $this;
	}

	public function send()
    {
        $emailTemplate = Mage::getModel('core/email_template');
        // Send all emails from corresponding list
        while (!empty($this->_emailInfos)) {
            $emailInfo = array_pop($this->_emailInfos);
            // Handle "Bcc" recepients of the current email
            $emailTemplate->addBcc($emailInfo->getBccEmails());
            //Check and attach PDF to order email
			if(!empty($this->_attachedFile)) {
				$emailTemplate->setAttachments($this->_attachedFile);
			}
			// Set required design parameters and delegate email sending to Mage_Core_Model_Email_Template
            $emailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $this->getStoreId()))
                ->sendTransactional(
                $this->getTemplateId(),
                $this->getSender(),
                $emailInfo->getToEmails(),
                $emailInfo->getToNames(),
                $this->getTemplateParams(),
                $this->getStoreId()
            );
        }
        return $this;
    }

    /**
     * Set email sender
     *
     * @param string|array $sender
     * @return Mage_Core_Model_Email_Template_Mailer
     */
    public function setSender($sender)
    {
        return $this->setData('sender', $sender);
    }

    /**
     * Get email sender
     *
     * @return string|array|null
     */
    public function getSender()
    {
        return $this->_getData('sender');
    }

    /**
     * Set store id
     *
     * @param int $storeId
     * @return Mage_Core_Model_Email_Template_Mailer
     */
    public function setStoreId($storeId)
    {
        return $this->setData('store_id', $storeId);
    }

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->_getData('store_id');
    }

    /**
     * Set template id
     *
     * @param int $templateId
     * @return Mage_Core_Model_Email_Template_Mailer
     */
    public function setTemplateId($templateId)
    {
        return $this->setData('template_id', $templateId);
    }

    /**
     * Get template id
     *
     * @return int|null
     */
    public function getTemplateId()
    {
        return $this->_getData('template_id');
    }

    /**
     * Set tempate parameters
     *
     * @param array $templateParams
     * @return Mage_Core_Model_Email_Template_Mailer
     */
    public function setTemplateParams(array $templateParams)
    {
        return $this->setData('template_params', $templateParams);
    }

    /**
     * Get template parameters
     *
     * @return array|null
     */
    public function getTemplateParams()
    {
        return $this->_getData('template_params');
    }
}
