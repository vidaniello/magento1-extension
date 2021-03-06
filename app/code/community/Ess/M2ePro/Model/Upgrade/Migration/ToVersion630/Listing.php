<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

// @codingStandardsIgnoreFile

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion630_Listing
{
    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    protected $_installer = null;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Upgrade_MySqlSetup
     */
    public function getInstaller()
    {
        return $this->_installer;
    }

    /**
     * @param Ess_M2ePro_Model_Upgrade_MySqlSetup $installer
     */
    public function setInstaller(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->_installer = $installer;
    }

    //########################################

    /**

        ALTER TABLE `m2epro_amazon_listing`
            DROP COLUMN `condition_note_custom_attribute`;

        ALTER TABLE `m2epro_buy_listing`
            DROP COLUMN `condition_note_custom_attribute`;

        ALTER TABLE `m2epro_play_listing`
            DROP COLUMN `condition_note_custom_attribute`;

     */

    //########################################

    public function process()
    {
        $this->processSku();
        $this->processCondition();
        $this->processConditionNote();
        $this->processBuyShipping();
    }

    //########################################

    protected function processSku()
    {
        $this->_installer->run(
            <<<SQL

    UPDATE `{$this->_installer->getTable('m2epro_amazon_listing')}`
    SET sku_mode = 1
    WHERE sku_mode = 0;

    UPDATE `{$this->_installer->getTable('m2epro_buy_listing')}`
    SET sku_mode = 1
    WHERE sku_mode = 0;

    UPDATE `{$this->_installer->getTable('m2epro_play_listing')}`
    SET sku_mode = 1
    WHERE sku_mode = 0;

SQL
        );
    }

    protected function processCondition()
    {
        $this->_installer->run(
            <<<SQL

    UPDATE `{$this->_installer->getTable('m2epro_amazon_listing')}`
    SET condition_mode = 1,
        condition_value = 'New'
    WHERE condition_mode = 0;

    UPDATE `{$this->_installer->getTable('m2epro_buy_listing')}`
    SET condition_mode = 1,
        condition_value = 1
    WHERE condition_mode = 0;

    UPDATE `{$this->_installer->getTable('m2epro_play_listing')}`
    SET condition_mode = 1,
        condition_value = 'New'
    WHERE condition_mode = 0;

SQL
        );
    }

    protected function processConditionNote()
    {
        $connection = $this->getInstaller()->getConnection();

        $tempTable = $this->getInstaller()->getTable('m2epro_amazon_listing');

        if ($connection->tableColumnExists($tempTable, 'condition_note_custom_attribute')) {
            $this->getInstaller()->run(
                <<<SQL

    UPDATE `{$this->_installer->getTable('m2epro_amazon_listing')}`
    SET    `condition_note_value` = CONCAT('#', `condition_note_custom_attribute`, '#'),
           `condition_note_mode` = 1
    WHERE  `condition_note_mode` = 2;

    UPDATE `{$this->_installer->getTable('m2epro_buy_listing')}`
    SET    `condition_note_value` = CONCAT('#', `condition_note_custom_attribute`, '#'),
           `condition_note_mode` = 1
    WHERE  `condition_note_mode` = 2;

    UPDATE `{$this->_installer->getTable('m2epro_play_listing')}`
    SET    `condition_note_value` = CONCAT('#', `condition_note_custom_attribute`, '#'),
           `condition_note_mode` = 1
    WHERE  `condition_note_mode` = 2;

SQL
            );
        }

        $this->getInstaller()->run(
            <<<SQL

    UPDATE `{$this->_installer->getTable('m2epro_amazon_listing')}`
    SET condition_note_mode = 3
    WHERE condition_note_mode = 0;

    UPDATE `{$this->_installer->getTable('m2epro_buy_listing')}`
    SET condition_note_mode = 3
    WHERE condition_note_mode = 0;

    UPDATE `{$this->_installer->getTable('m2epro_play_listing')}`
    SET condition_note_mode = 3
    WHERE condition_note_mode = 0;

SQL
        );

        $tempTable = $this->getInstaller()->getTable('m2epro_amazon_listing');

        if ($connection->tableColumnExists($tempTable, 'condition_note_custom_attribute') !== false) {
            $connection->dropColumn($tempTable, 'condition_note_custom_attribute');
        }

        $tempTable = $this->getInstaller()->getTable('m2epro_buy_listing');

        if ($connection->tableColumnExists($tempTable, 'condition_note_custom_attribute') !== false) {
            $connection->dropColumn($tempTable, 'condition_note_custom_attribute');
        }

        $tempTable = $this->getInstaller()->getTable('m2epro_play_listing');

        if ($connection->tableColumnExists($tempTable, 'condition_note_custom_attribute') !== false) {
            $connection->dropColumn($tempTable, 'condition_note_custom_attribute');
        }
    }

    protected function processBuyShipping()
    {
        $this->_installer->run(
            <<<SQL

UPDATE `{$this->_installer->getTable('m2epro_buy_listing')}`
SET shipping_standard_mode = 3
WHERE shipping_standard_mode = 0;

UPDATE `{$this->_installer->getTable('m2epro_buy_listing')}`
SET shipping_expedited_mode = 3
WHERE shipping_expedited_mode = 0;

UPDATE `{$this->_installer->getTable('m2epro_buy_listing')}`
SET shipping_one_day_mode = 3
WHERE shipping_one_day_mode = 0;

UPDATE `{$this->_installer->getTable('m2epro_buy_listing')}`
SET shipping_two_day_mode = 3
WHERE shipping_two_day_mode = 0;

SQL
        );
    }

    //########################################
}
