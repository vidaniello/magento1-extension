<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Item extends Ess_M2ePro_Model_Component_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Account
     */
    protected $_accountModel = null;

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    protected $_marketplaceModel = null;

    /**
     * @var Ess_M2ePro_Model_Magento_Product
     */
    protected $_magentoProductModel = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Item');
    }

    //########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->_accountModel = null;
        $temp && $this->_marketplaceModel = null;
        $temp && $this->_magentoProductModel = null;
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if ($this->_accountModel === null) {
            $this->_accountModel = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
                'Account', $this->getAccountId()
            );
        }

        return $this->_accountModel;
    }

    /**
     * @param Ess_M2ePro_Model_Account $instance
     */
    public function setAccount(Ess_M2ePro_Model_Account $instance)
    {
         $this->_accountModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if ($this->_marketplaceModel === null) {
            $this->_marketplaceModel = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
                'Marketplace', $this->getMarketplaceId()
            );
        }

        return $this->_marketplaceModel;
    }

    /**
     * @param Ess_M2ePro_Model_Marketplace $instance
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplace $instance)
    {
         $this->_marketplaceModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        if ($this->_magentoProductModel) {
            return $this->_magentoProductModel;
        }

        return $this->_magentoProductModel = Mage::getModel('M2ePro/Magento_Product')
                                                 ->setStoreId($this->getStoreId())
                                                 ->setProductId($this->getProductId());
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product $instance
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $instance)
    {
        $this->_magentoProductModel = $instance;
    }

    //########################################

    /**
     * @return int
     */
    public function getAccountId()
    {
        return (int)$this->getData('account_id');
    }

    /**
     * @return int
     */
    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    public function getSku()
    {
        return $this->getData('sku');
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return (int)$this->getData('product_id');
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return (int)$this->getData('store_id');
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getVariationProductOptions()
    {
        return $this->getSettings('variation_product_options');
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getVariationChannelOptions()
    {
        return $this->getSettings('variation_channel_options');
    }

    /**
     * @return int
     */
    public function getGroupedProductMode()
    {
        return (int)$this->getSetting(
            'additional_data',
            'grouped_product_mode',
            Ess_M2ePro_Model_Listing_Product::GROUPED_PRODUCT_MODE_OPTIONS
        );
    }

    /**
     * @return bool
     */
    public function isGroupedProductModeSet()
    {
        return $this->getGroupedProductMode() == Ess_M2ePro_Model_Listing_Product::GROUPED_PRODUCT_MODE_SET;
    }

    //########################################
}
