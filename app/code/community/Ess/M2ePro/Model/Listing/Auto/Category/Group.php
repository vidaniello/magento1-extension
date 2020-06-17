<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_Auto_Category_Group extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    /** @var Ess_M2ePro_Model_ActiveRecord_Factory */
    protected $_activeRecordFactory;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Auto_Category_Group');
        $this->_activeRecordFactory = Mage::getSingleton('M2ePro/ActiveRecord_Factory');
    }

    //########################################

    /**
     * @return int
     */
    public function getListingId()
    {
        return (int)$this->getData('listing_id');
    }

    //########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    //########################################

    /**
     * @return int
     */
    public function getAddingMode()
    {
        return (int)$this->getData('adding_mode');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isAddingModeNone()
    {
        return $this->getAddingMode() == Ess_M2ePro_Model_Listing::ADDING_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isAddingModeAdd()
    {
        return $this->getAddingMode() == Ess_M2ePro_Model_Listing::ADDING_MODE_ADD;
    }

    /**
     * @return bool
     */
    public function isAddingAddNotVisibleYes()
    {
        return $this->getData('adding_add_not_visible') == Ess_M2ePro_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES;
    }

    //########################################

    /**
     * @return int
     */
    public function getDeletingMode()
    {
        return (int)$this->getData('deleting_mode');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isDeletingModeNone()
    {
        return $this->getDeletingMode() == Ess_M2ePro_Model_Listing::DELETING_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isDeletingModeStop()
    {
        return $this->getDeletingMode() == Ess_M2ePro_Model_Listing::DELETING_MODE_STOP;
    }

    /**
     * @return bool
     */
    public function isDeletingModeStopRemove()
    {
        return $this->getDeletingMode() == Ess_M2ePro_Model_Listing::DELETING_MODE_STOP_REMOVE;
    }

    //########################################

    /**
     * @param bool $asObjects
     * @return array|Ess_M2ePro_Model_Listing_Auto_Category[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getCategories($asObjects = false)
    {
        $collection = $this->_activeRecordFactory->getObjectCollection('Listing_Auto_Category');
        $collection->addFieldToFilter('group_id', $this->getId());

        if (!$asObjects) {
            $result = $collection->toArray();
            return $result['items'];
        }

        return $collection->getItems();
    }

    public function clearCategories()
    {
        $categories = $this->getCategories(true);
        foreach ($categories as $category) {
            $category->deleteInstance();
        }
    }

    //########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        foreach ($this->getCategories(true) as $item) {
            $item->deleteInstance();
        }

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    //########################################
}
