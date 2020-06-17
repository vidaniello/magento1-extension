<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_AutoAction_Mode_Global
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_GlobalAbstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('walmartListingAutoActionModeGlobal');
        $this->setTemplate('M2ePro/walmart/listing/auto_action/mode/global.phtml');
    }

    //########################################
}
