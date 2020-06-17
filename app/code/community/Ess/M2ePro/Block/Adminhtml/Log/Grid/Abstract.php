<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Log_Grid_Abstract
    extends Mage_Adminhtml_Block_Widget_Grid
{
    const LISTING_ID_FIELD = 'listing_id';
    const LISTING_PRODUCT_ID_FIELD = 'listing_product_id';
    const LISTING_PARENT_PRODUCT_ID_FIELD = 'parent_listing_product_id';

    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct;

    //########################################

    protected function getEntityId()
    {
        if ($this->isListingLog()) {
            return $this->getRequest()->getParam('id');
        }

        if ($this->isListingProductLog()) {
            return $this->getRequest()->getParam('listing_product_id');
        }

        return null;
    }

    protected function getEntityField()
    {
        if ($this->isListingLog()) {
            return self::LISTING_ID_FIELD;
        }

        if ($this->isListingProductLog()) {
            return self::LISTING_PRODUCT_ID_FIELD;
        }

        return null;
    }

    protected function getActionName()
    {
        switch ($this->getEntityField()) {
            case self::LISTING_ID_FIELD:
                return 'listingGrid';

            case self::LISTING_PRODUCT_ID_FIELD:
                return 'listingProductGrid';
        }

        return 'listingGrid';
    }

    //########################################

    public function isListingLog()
    {
        $id = $this->getRequest()->getParam('id');
        return !empty($id);
    }

    public function isListingProductLog()
    {
        $listingProductId = $this->getRequest()->getParam('listing_product_id');
        return !empty($listingProductId);
    }

    //########################################

    protected function addMaxAllowedLogsCountExceededNotification($date)
    {
        $notification = Mage::helper('M2ePro')->__(
            'Using a Grouped View Mode, the logs records which are not older than %date% are
            displayed here in order to prevent any possible Performance-related issues.',
            $this->formatDate($date, IntlDateFormatter::MEDIUM, true)
        );

        $this->getMessagesBlock()->addNotice($notification);
    }

    protected function getMaxRecordsCount()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/logs/grouped/', 'max_records_count'
        );
    }

    //########################################

    public function getListingProductId()
    {
        return $this->getRequest()->getParam('listing_product_id', false);
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing_Product|null
     */
    public function getListingProduct()
    {
        if ($this->_listingProduct === null) {
            $this->_listingProduct = Mage::helper('M2ePro/Component')
                                         ->getUnknownObject('Listing_Product', $this->getListingProductId());
        }

        return $this->_listingProduct;
    }

    //########################################

    protected function _setCollectionOrder($column)
    {
        // We need to sort by id to maintain the correct sequence of records
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            $collection->getSelect()->order($columnIndex . ' ' . strtoupper($column->getDir()))->order('id DESC');
        }

        return $this;
    }

    //########################################

    protected function _getLogTypeList()
    {
        return array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE => Mage::helper('M2ePro')->__('Notice'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => Mage::helper('M2ePro')->__('Success'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => Mage::helper('M2ePro')->__('Warning'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => Mage::helper('M2ePro')->__('Error')
        );
    }

    protected function _getLogInitiatorList()
    {
        return array(
            Ess_M2ePro_Helper_Data::INITIATOR_USER => Mage::helper('M2ePro')->__('Manual'),
            Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION => Mage::helper('M2ePro')->__('Automatic')
        );
    }

    //########################################

    public function callbackColumnType($value, $row, $column, $isExport)
    {
         switch ($row->getData('type')) {
            case Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE:
                break;

            case Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS:
                $value = '<span style="color: green;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING:
                $value = '<span style="color: orange; font-weight: bold;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Synchronization_Log::TYPE_FATAL_ERROR:
            case Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR:
                 $value = '<span style="color: red; font-weight: bold;">'.$value.'</span>';
                break;

            default:
                break;
         }

        return $value;
    }

    public function callbackColumnInitiator($value, $row, $column, $isExport)
    {
        switch ($row->getData('initiator')) {
            case Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION:
                $value = '<span style="text-decoration: underline;">'.$value.'</span>';
                break;

            default:
                break;
        }

        return $value;
    }

    public function callbackDescription($value, $row, $column, $isExport)
    {
        $fullDescription = str_replace(
            "\n",
            '<br>',
            Mage::helper('M2ePro/View')->getModifiedLogMessage($value)
        );

        $renderedText = $this->stripTags($fullDescription, '<br>');
        if (strlen($renderedText) < 200) {
            return $fullDescription;
        }

        $renderedText =  Mage::helper('core/string')->truncate($renderedText, 200, '');
        $renderedText .= '&nbsp;(<a href="javascript:void(0)" onclick="LogObj.showFullText(this);">more</a>)
                          <div style="display: none;"><br/>'.$fullDescription.'<br/><br/></div>';

        return $renderedText;
    }

    //########################################

    abstract protected function getActionTitles();

    //########################################
}
