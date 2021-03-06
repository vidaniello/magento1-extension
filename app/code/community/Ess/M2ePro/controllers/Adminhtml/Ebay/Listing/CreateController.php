<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_Listing_CreateController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Creating A New M2E Pro Listing'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addCss('M2ePro/css/Plugin/DropDown.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addJs('M2ePro/Attribute.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/SynchProgress.js')
            ->addJs('M2ePro/Ebay/Listing/MarketplaceSynchProgress.js')
            ->addJs('M2ePro/TemplateManager.js')
            ->addJs('M2ePro/Ebay/Listing/Template/Switcher.js')
            ->addJs('M2ePro/Ebay/Template/Payment.js')
            ->addJs('M2ePro/Ebay/Template/Return.js')
            ->addJs('M2ePro/Ebay/Template/Shipping.js')
            ->addJs('M2ePro/Ebay/Template/Shipping/ExcludedLocations.js')
            ->addJs('M2ePro/Ebay/Template/SellingFormat.js')
            ->addJs('M2ePro/Ebay/Template/Description.js')
            ->addJs('M2ePro/Ebay/Template/Synchronization.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "x/ZQAJAQ");

        if (Mage::helper('M2ePro/Magento')->isTinyMceAvailable()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK . '/listings'
        );
    }

    //########################################

    public function indexAction()
    {
        $step = (int)$this->getRequest()->getParam('step');

        switch ($step) {
            case 1:
                $this->stepOne();
                break;
            case 2:
                $this->stepTwo();
                break;
            case 3:
                $this->stepThree();
                break;
            case 4:
                $this->stepFour();
                break;
            default:
                $this->clearSession();
                $this->_redirect('*/*/index', array('_current' => true, 'step' => 1));
                break;
        }
    }

    //########################################

    protected function stepOne()
    {
        if ($this->getRequest()->getParam('clear')) {
            $this->clearSession();
            $this->getRequest()->setParam('clear', null);
            $this->_redirect('*/*/index', array('_current' => true, 'step' => 1));
            return;
        }

        $this->setWizardStep('listingAccount');

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            // clear session data if user came back to the first step and changed the marketplace
            // ---------------------------------------
            if ($this->getSessionValue('marketplace_id')
                && (int)$this->getSessionValue('marketplace_id') != (int)$post['marketplace_id']
            ) {
                $this->clearSession();
            }

            $this->setSessionValue('listing_title', strip_tags($post['title']));
            $this->setSessionValue('account_id', (int)$post['account_id']);
            $this->setSessionValue('marketplace_id', (int)$post['marketplace_id']);
            $this->setSessionValue('store_id', (int)$this->getRequest()->getPost('store_id'));

            $this->_redirect('*/*/index', array('_current' => true, 'step' => 2));
            return;
        }

        $listingOnlyMode = Ess_M2ePro_Helper_View::LISTING_CREATION_MODE_LISTING_ONLY;
        if ($this->getRequest()->getParam('creation_mode') == $listingOnlyMode) {
            $this->setSessionValue('creation_mode', $listingOnlyMode);
        }

        $this->_initAction();
        $this->setPageHelpLink(null, null, "x/ZQAJAQ");

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_accountMarketplace'));
        $this->renderLayout();
    }

    protected function stepTwo()
    {
        if ($this->getSessionValue('account_id') === null ||
            $this->getSessionValue('marketplace_id') === null
        ) {
            $this->clearSession();
            $this->_redirect('*/*/index', array('_current' => true, 'step' => 1));
            return;
        }

        $this->setWizardStep('listingGeneral');

        $templateNicks = array(
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN_POLICY,
        );

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            foreach ($templateNicks as $nick) {
                $templateData = Mage::helper('M2ePro')->jsonDecode(base64_decode($post["template_{$nick}"]));
                $this->setSessionValue("template_id_{$nick}", $templateData['id']);
                $this->setSessionValue("template_mode_{$nick}", $templateData['mode']);
            }

            $this->_redirect('*/*/index', array('_current' => true, 'step' => 3));
            return;
        }

        $this->loadTemplatesDataFromSession();

        $data = array(
            'allowed_tabs' => array('general')
        );
        $content = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_template_edit');
        $content->setData($data);

        $this->_initAction();
        $this->setPageHelpLink(null, null, "x/ZQAJAQ");

        $this->_addContent($content);
        $this->renderLayout();
    }

    protected function stepThree()
    {
        if ($this->getSessionValue('account_id') === null ||
            $this->getSessionValue('marketplace_id') === null
        ) {
            $this->clearSession();
            $this->_redirect('*/*/index', array('_current' => true, 'step' => 1));
            return;
        }

        $this->setWizardStep('listingSelling');

        $templateNicks = array(
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION,
        );

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            foreach ($templateNicks as $nick) {
                $templateData = Mage::helper('M2ePro')->jsonDecode(base64_decode($post["template_{$nick}"]));
                $this->setSessionValue("template_id_{$nick}", $templateData['id']);
                $this->setSessionValue("template_mode_{$nick}", $templateData['mode']);
            }

            $this->_redirect('*/*/index', array('_current' => true, 'step' => 4));
            return;
        }

        $this->loadTemplatesDataFromSession();

        $data = array(
            'allowed_tabs' => array('selling')
        );
        $content = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_template_edit');
        $content->setData($data);

        $this->_initAction();
        $this->setPageHelpLink(null, null, "x/ZQAJAQ");

        $this->_addContent($content);
        $this->renderLayout();
    }

    protected function stepFour()
    {
        if ($this->getSessionValue('account_id') === null ||
            $this->getSessionValue('marketplace_id') === null
        ) {
            $this->clearSession();
            $this->_redirect('*/*/index', array('step' => 1,'_current' => true));
            return;
        }

        $this->setWizardStep('listingSynchronization');

        $templateNicks = array(
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION,
        );

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            foreach ($templateNicks as $nick) {
                // @codingStandardsIgnoreLine
                $templateData = Mage::helper('M2ePro')->jsonDecode(base64_decode($post["template_{$nick}"]));
                $this->setSessionValue("template_id_{$nick}", $templateData['id']);
                $this->setSessionValue("template_mode_{$nick}", $templateData['mode']);
            }

            $listing = $this->createListing();

            //todo Transferring move in another place?
            if ($listingId = $this->getRequest()->getParam('listing_id')) {
                /** @var Ess_M2ePro_Model_Ebay_Listing_Transferring $transferring */
                $transferring = Mage::getModel('M2ePro/Ebay_Listing_Transferring');
                $transferring->setListing(
                    Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId)
                );

                $this->clearSession();
                $transferring->setTargetListingId($listing->getId());

                return $this->_redirect(
                    '*/adminhtml_ebay_listing_transferring/index',
                    array(
                        'listing_id' => $listingId,
                        'step'       => 3,
                    )
                );
            }

            if ($this->isCreationModeListingOnly()) {
                // closing window for 3rd party products moving in new listing creation
                return $this->getResponse()->setBody("<script>window.close();</script>");
            }

            $this->clearSession();

            if ((bool)$this->getRequest()->getParam('wizard', false)) {
                $this->setWizardStep('sourceMode');
                return $this->_redirect('*/adminhtml_wizard_installationEbay');
            }

            return $this->_redirect(
                '*/adminhtml_ebay_listing_productAdd/sourceMode',
                array(
                    'listing_id'       => $listing->getId(),
                    'listing_creation' => true
                )
            );
        }

        $this->loadTemplatesDataFromSession();

        $data = array(
            'allowed_tabs' => array('synchronization')
        );
        $content = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_template_edit');
        $content->setData($data);

        $this->_initAction();
        $this->setPageHelpLink(null, null, "x/ZQAJAQ");

        $this->_addContent($content);
        $this->renderLayout();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function createListing()
    {
        $data = array();
        $data['title'] = $this->getSessionValue('listing_title');
        $data['account_id'] = $this->getSessionValue('account_id');
        $data['marketplace_id'] = $this->getSessionValue('marketplace_id');
        $data['store_id'] = $this->getSessionValue('store_id');

        /** @var Ess_M2ePro_Model_Marketplace $marketplace */
        $marketplace = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Marketplace', $data['marketplace_id']);

        $data['parts_compatibility_mode'] = null;
        if ($marketplace->getChildObject()->isMultiMotorsEnabled()) {
            $data['parts_compatibility_mode'] = Ess_M2ePro_Model_Ebay_Listing::PARTS_COMPATIBILITY_MODE_KTYPES;
        }

        foreach (Mage::getSingleton('M2ePro/Ebay_Template_Manager')->getAllTemplates() as $nick) {
            /** @var Ess_M2ePro_Model_Ebay_Template_Manager $manager */
            $manager = Mage::getModel('M2ePro/Ebay_Template_Manager')
                ->setTemplate($nick);

            $templateId = $this->getSessionValue("template_id_{$nick}");
            $templateMode = $this->getSessionValue("template_mode_{$nick}");

            $idColumn = $manager->getIdColumnNameByMode($templateMode);
            $modeColumn = $manager->getModeColumnName();

            $data[$idColumn] = $templateId;
            $data[$modeColumn] = $templateMode;
        }

        $model = Mage::helper('M2ePro/Component_Ebay')->getModel('Listing');
        $model->addData($data);
        $model->save();

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
        $tempLog->addListingMessage(
            $model->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_USER,
            $tempLog->getResource()->getNextActionId(),
            Ess_M2ePro_Model_Listing_Log::ACTION_ADD_LISTING,
            'Listing was Added',
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE
        );

        return $model;
    }

    //########################################

    protected function loadTemplatesDataFromSession()
    {
        Mage::helper('M2ePro/Data_Global')->setValue(
            'ebay_custom_template_title',
            $this->getSessionValue('listing_title')
        );

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Switcher_DataLoader $dataLoader */
        $dataLoader = Mage::getBlockSingleton('M2ePro/adminhtml_ebay_listing_template_switcher_dataLoader');
        $dataLoader->load(
            Mage::helper('M2ePro/Data_Session'),
            array('session_key' => Ess_M2ePro_Model_Ebay_Listing::CREATE_LISTING_SESSION_DATA)
        );
    }

    //########################################

    protected function setSessionValue($key, $value)
    {
        $sessionData = $this->getSessionValue();
        $sessionData[$key] = $value;

        Mage::helper('M2ePro/Data_Session')->setValue(
            Ess_M2ePro_Model_Ebay_Listing::CREATE_LISTING_SESSION_DATA,
            $sessionData
        );
        return $this;
    }

    protected function getSessionValue($key = null)
    {
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue(
            Ess_M2ePro_Model_Ebay_Listing::CREATE_LISTING_SESSION_DATA
        );

        if ($sessionData === null) {
            $sessionData = array();
        }

        if ($key === null) {
            return $sessionData;
        }

        return isset($sessionData[$key]) ? $sessionData[$key] : null;
    }

    //########################################

    protected function clearSession()
    {
        Mage::helper('M2ePro/Data_Session')->setValue(Ess_M2ePro_Model_Ebay_Listing::CREATE_LISTING_SESSION_DATA, null);
    }

    //########################################

    protected function setWizardStep($step)
    {
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');
        if (!$wizardHelper->isActive(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK)) {
            return;
        }

        $wizardHelper->setStep(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK, $step);
    }

    //########################################

    protected function isCreationModeListingOnly()
    {
        return $this->getSessionValue('creation_mode') === Ess_M2ePro_Helper_View::LISTING_CREATION_MODE_LISTING_ONLY;
    }

    //########################################
}
