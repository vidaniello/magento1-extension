<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Account_Repricing_AffectedListingsProducts
    extends Ess_M2ePro_Model_Template_AffectedListingsProductsAbstract
{
    //########################################

    public function loadCollection(array $filters = array())
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Collection $listingCollection */
        $listingCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing');
        $listingCollection->addFieldToFilter('account_id', $this->_model->getAccountId());
        $listingCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingCollection->getSelect()->columns('id');

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('listing_id', array('in' => $listingCollection->getSelect()));
        $listingProductCollection->addFieldToFilter('is_variation_parent', 0);
        $listingProductCollection->addFieldToFilter('is_repricing', 1);

        return $listingProductCollection;
    }

    //########################################
}
