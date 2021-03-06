<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation_ListingTutorial
    extends Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation
{
    //########################################

    protected function getStep()
    {
        return 'listingTutorial';
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $this->updateButton('continue', 'label', Mage::helper('M2ePro')->__('Create First Listing'));
        $this->updateButton('continue', 'class', 'primary');
    }

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->add(
            $this->getUrl('*/adminhtml_ebay_listing_create/index'), 'ebay_listing_create'
        );

        return parent::_prepareLayout();
    }

    //########################################
}
