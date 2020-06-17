<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Product_Grid
    extends Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Manually_Grid
{
    //########################################

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/adminhtml_ebay_listing_categorySettings/stepTwoModeProductGrid',
            array(
                '_current' => true
            )
        );
    }

    //########################################

    protected function _toHtml()
    {
        $additionalJs = '';
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $additionalJs = <<<HTML
<script type="text/javascript">

    Event.observe(window, 'load', function() {
        EbayListingCategoryProductGridObj.getSuggestedCategoriesForAll();
    });

</script>
HTML;
        }

        return parent::_toHtml() . $additionalJs;
    }

    //########################################
}
