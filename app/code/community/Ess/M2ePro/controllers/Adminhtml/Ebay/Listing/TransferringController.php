<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_Listing_TransferringController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    /** @var Ess_M2ePro_Model_Ebay_Listing_Transferring */
    protected $_transferring;

    //########################################

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK . '/listings'
        );
    }

    //########################################

    public function indexAction()
    {
        $this->initialize();

        $productsIds = $this->getRequest()->getParam('products_ids');
        if (!empty($productsIds)) {
            $this->_transferring->clearSession();
            $this->_transferring->setProductsIds(explode(',', $productsIds));
        }

        switch ((int)$this->getRequest()->getParam('step')) {
            case 1:
                $this->destinationStep();
                break;

            case 2:
                $this->listingStep();
                break;

            case 3:
                $this->productsStep();
                break;

            default:
                return $this->_redirect('*/*/index', array('_current' => true, 'step' => 1));
        }
    }

    //########################################

    protected function destinationStep()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Transferring_Destination $block */
        $block = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_transferring_destination',
            '',
            array(
                'listing' => $this->_listing
            )
        );

        $this->getResponse()->setBody($block->toHtml());
    }

    protected function listingStep()
    {
        $this->_transferring->setTargetListingId($this->getRequest()->getParam('to_listing_id'));

        if (!$this->_transferring->isTargetListingNew()) {
            return $this->_redirect(
                '*/*/index',
                array(
                    'listing_id' => $this->_listing->getId(),
                    'step'       => 3
                )
            );
        }

        $manager = Mage::getModel('M2ePro/Ebay_Template_Manager');
        $templates = $this->_listing->getMarketplaceId() == $this->getRequest()->getParam('marketplace_id')
            ? $manager->getAllTemplates()
            : $manager->getNotMarketplaceDependentTemplates();

        $sessionData = array(
            'account_id'     => (int)$this->getRequest()->getParam('account_id'),
            'marketplace_id' => (int)$this->getRequest()->getParam('marketplace_id'),
            'store_id'       => (int)$this->getRequest()->getParam('store_id')
        );

        foreach ($templates as $nick) {
            $manager->setTemplate($nick);
            $sessionData["template_id_{$nick}"] = $this->_listing->getData($manager->getTemplateIdColumnName());
            $sessionData["template_mode_{$nick}"] = $this->_listing->getData($manager->getModeColumnName());
        }

        Mage::helper('M2ePro/Data_Session')->setValue(
            Ess_M2ePro_Model_Ebay_Listing::CREATE_LISTING_SESSION_DATA,
            $sessionData
        );

        return $this->_redirect(
            '*/adminhtml_ebay_listing_create/index',
            array(
                '_current'      => true,
                'step'          => 1,
                'creation_mode' => Ess_M2ePro_Helper_View::LISTING_CREATION_MODE_LISTING_ONLY
            )
        );
    }

    protected function productsStep()
    {
        $this->loadLayout();

        $this->getLayout()->getBlock('head')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Ebay/Listing/Transferring.js');

        $this->_title(Mage::helper('M2ePro')->__('Sell on Another Marketplace'))
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_ebay_listing_transferring_products',
                    '',
                    array(
                        'listing' => $this->_listing
                    )
                )
            )
            ->renderLayout();
    }

    //########################################

    public function getListingsAction()
    {
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing')
            ->addFieldToFilter('id', array('neq' => (int)$this->getRequest()->getParam('listing_id')))
            ->addFieldToFilter('account_id', (int)$this->getRequest()->getParam('account_id'))
            ->addFieldToFilter('marketplace_id', (int)$this->getRequest()->getParam('marketplace_id'))
            ->addFieldToFilter('store_id', (int)$this->getRequest()->getParam('store_id'));

        $listings = array();
        foreach ($collection->getItems() as $listing) {
            $listings[] = array(
                'id'    => $listing->getId(),
                'title' => Mage::helper('M2ePro')->escapeHtml($listing->getTitle())
            );
        }

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($listings));
    }

    public function addProductsAction()
    {
        $this->initialize();

        /** @var Ess_M2ePro_Model_Listing $targetListing */
        $targetListing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing',
            $this->_transferring->getTargetListingId()
        );

        $isDifferentMarketplaces = $targetListing->getMarketplaceId() != $this->_listing->getMarketplaceId();

        $productsIds = $this->getRequest()->getParam('products');
        $productsIds = explode(',', $productsIds);
        $productsIds = array_unique($productsIds);
        $productsIds = array_filter($productsIds);

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('id', array('in' => ($productsIds)));

        $ids = array();
        foreach ($collection->getItems() as $sourceListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $sourceListingProduct */
            $listingProduct = $targetListing->getChildObject()->addProductFromAnotherEbaySite(
                $sourceListingProduct, $this->_listing
            );

            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                $this->_transferring->setErrorsCount($this->_transferring->getErrorsCount() + 1);
                continue;
            }

            $ids[] = $listingProduct->getId();
        }

        if ($isDifferentMarketplaces) {
            $existingIds = $targetListing->getChildObject()->getAddedListingProductsIds();
            $existingIds = array_values(array_unique(array_merge($existingIds, $ids)));

            $targetListing->setData('product_add_ids', Mage::helper('M2ePro')->jsonEncode($existingIds));
            $targetListing->save();
        }

        if ($this->getRequest()->getParam('is_last_part')) {
            if ($this->_transferring->getErrorsCount()) {
                $this->getSession()->addError(
                    Mage::helper('M2ePro')->__(
                        '%errors_count% product(s) were not added to the selected Listing. Please
                        <a target="_blank" href="%url%">view Log</a> for the details.',
                        $this->_transferring->getErrorsCount(),
                        $this->getUrl(
                            '*/adminhtml_ebay_log/listing',
                            array('listing_id' => $this->_listing->getId())
                        )
                    )
                );
            }

            $this->_transferring->clearSession();
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => 'success')));
    }

    //########################################

    protected function initialize()
    {
        $this->_listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing',
            $this->getRequest()->getParam('listing_id')
        );

        $this->_transferring = Mage::getModel('M2ePro/Ebay_Listing_Transferring');
        $this->_transferring->setListing($this->_listing);
    }

    //########################################
}
