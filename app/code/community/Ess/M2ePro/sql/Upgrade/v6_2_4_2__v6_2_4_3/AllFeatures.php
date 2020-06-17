<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_2_4_2__v6_2_4_3_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        /** @var Ess_M2ePro_Model_Upgrade_Migration_ToVersion630 $migrationInstance */
        $migrationInstance = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion630');
        $migrationInstance->setInstaller($installer);
        $migrationInstance->migrate();

        $tempTable = $installer->getTable('m2epro_amazon_listing_product');

        if ($connection->tableColumnExists($tempTable, 'start_date') !== false) {
            $connection->dropColumn($tempTable, 'start_date');
        }

        if ($connection->tableColumnExists($tempTable, 'end_date') !== false) {
            $connection->dropColumn($tempTable, 'end_date');
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_amazon_listing_other');

        if ($connection->tableColumnExists($tempTable, 'start_date') !== false) {
            $connection->dropColumn($tempTable, 'start_date');
        }

        if ($connection->tableColumnExists($tempTable, 'end_date') !== false) {
            $connection->dropColumn($tempTable, 'end_date');
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_buy_listing_product');

        if ($connection->tableColumnExists($tempTable, 'start_date') !== false) {
            $connection->dropColumn($tempTable, 'start_date');
        }

        if ($connection->tableColumnExists($tempTable, 'end_date') !== false) {
            $connection->dropColumn($tempTable, 'end_date');
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_buy_listing_other');

        if ($connection->tableColumnExists($tempTable, 'end_date') !== false) {
            $connection->dropColumn($tempTable, 'end_date');
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_play_listing_product');

        if ($connection->tableColumnExists($tempTable, 'start_date') !== false) {
            $connection->dropColumn($tempTable, 'start_date');
        }

        if ($connection->tableColumnExists($tempTable, 'end_date') !== false) {
            $connection->dropColumn($tempTable, 'end_date');
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_play_listing_other');

        if ($connection->tableColumnExists($tempTable, 'start_date') !== false) {
            $connection->dropColumn($tempTable, 'start_date');
        }

        if ($connection->tableColumnExists($tempTable, 'end_date') !== false) {
            $connection->dropColumn($tempTable, 'end_date');
        }

        //########################################

        $installer->run(<<<SQL

  UPDATE `{$this->_installer->getTable('m2epro_ebay_template_category_specific')}`
  SET    `value_ebay_recommended` = '',
         `value_custom_value` = '',
         `value_custom_attribute` = ''
  WHERE `value_mode` = 0; -- NONE --

  UPDATE `{$this->_installer->getTable('m2epro_ebay_template_category_specific')}`
  SET    `value_custom_value` = '',
         `value_custom_attribute` = ''
  WHERE `value_mode` = 1; -- EBAY RECOMMENDED --

  UPDATE `{$this->_installer->getTable('m2epro_ebay_template_category_specific')}`
  SET    `value_ebay_recommended` = '',
         `value_custom_attribute` = ''
  WHERE `value_mode` = 2; -- CUSTOM VALUE --

  UPDATE `{$this->_installer->getTable('m2epro_ebay_template_category_specific')}`
  SET    `value_ebay_recommended` = '',
         `value_custom_value` = ''
  WHERE `value_mode` = 3; -- CUSTOM ATTRIBUTE --

  UPDATE `{$this->_installer->getTable('m2epro_ebay_template_category_specific')}`
  SET    `value_ebay_recommended` = '',
         `value_custom_value` = ''
  WHERE `value_mode` = 4; -- CUSTOM LABEL ATTRIBUTE --

  UPDATE `{$this->_installer->getTable('m2epro_ebay_template_category_specific')}`
  SET   `attribute_title` = ''
  WHERE `mode` = 3             -- CUSTOM ITEM SPECIFICS --
  AND   `value_mode` = 3;      -- CUSTOM ATTRIBUTE --

SQL
        );

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_dictionary_category');

        if ($connection->tableColumnExists($tempTable, 'attribute_set_id') !== false) {
            $connection->dropColumn($tempTable, 'attribute_set_id');
        }

        if ($connection->tableColumnExists($tempTable, 'attribute_set') !== false) {
            $connection->dropColumn($tempTable, 'attribute_set');
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_template_category_specific');

        if ($connection->tableColumnExists($tempTable, 'mode_relation_id') !== false) {
            $connection->dropColumn($tempTable, 'mode_relation_id');
        }

        if ($connection->tableColumnExists($tempTable, 'attribute_id') !== false) {
            $connection->dropColumn($tempTable, 'attribute_id');
        }

        // ---------------------------------------

        $installer->run(<<<SQL

  DELETE FROM `{$this->_installer->getTable('m2epro_ebay_template_category_specific')}`
  WHERE `mode` = 2;

  UPDATE `{$this->_installer->getTable('m2epro_ebay_dictionary_category')}`
  SET `item_specifics` = NULL;

SQL
        );

        $tempTable = $installer->getTable('m2epro_ebay_template_category_specific');

        $specifics = $connection->query("
            SELECT `id`, `value_ebay_recommended`
            FROM   `{$tempTable}`
            WHERE  `value_ebay_recommended` != '[]'
            AND    `value_ebay_recommended` != ''
        ")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($specifics as $specific) {

            $values = json_decode($specific['value_ebay_recommended'], true);

            foreach ($values as &$value) {
                $value = $value['value'];
            }

            $id = (int)$specific['id'];
            $values = $connection->quote(json_encode($values));

            $installer->run(<<<SQL

    UPDATE `{$this->_installer->getTable('m2epro_ebay_template_category_specific')}`
    SET `value_ebay_recommended` = {$values}
    WHERE `id` = {$id};

SQL
            );
        }

        //########################################

        $installer->run(<<<SQL

    DELETE FROM `{$this->_installer->getTable('m2epro_config')}`
    WHERE `group` = '/view/analytic/';

    DELETE FROM `{$this->_installer->getTable('m2epro_synchronization_config')}`
    WHERE `group` = '/ebay/marketplaces/motors_ktypes/'
    AND   `key`   = 'part_size';

    DELETE FROM `{$this->_installer->getTable('m2epro_synchronization_config')}`
    WHERE `group` = '/ebay/marketplaces/motors_specifics/'
    AND   `key`   = 'part_size';

SQL
        );

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_config');

        $tempRow = $connection->query("
    SELECT *
    FROM `{$tempTable}`
    WHERE `group` = '/cron/magento/' AND `key` = 'disabled'
")->fetch();

        if ($tempRow === false) {

            $installer->run(<<<SQL

INSERT INTO `{$this->_installer->getTable('m2epro_config')}` 
(`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/cron/magento/', 'disabled', '0', NULL, '2015-02-05 00:00:00', '2015-02-05 00:00:00');

SQL
            );
        }

        // ---------------------------------------

        $synchronizationTable = $installer->getTable('m2epro_synchronization_config');

        $tempRow = $connection->query("
            SELECT *
            FROM `{$synchronizationTable}`
            WHERE `group` = '/ebay/other_listing/synchronization/'
            AND   `key` = 'mode'
        ")->fetch();

        if ($tempRow === false) {

            $accountTable = $installer->getTable('m2epro_ebay_account');

            $isTableColumnExists = $connection->tableColumnExists(
                $accountTable, 'other_listings_synchronization_mapped_items_mode'
            );
            if ($isTableColumnExists !== false) {
                $mode = (int)$connection->query("
                    SELECT MIN(`other_listings_synchronization_mapped_items_mode`)
                    FROM `{$accountTable}`
                ")->fetchColumn();
            } else {
                $mode = 0;
            }

            $installer->run(<<<SQL

INSERT INTO `{$this->_installer->getTable('m2epro_synchronization_config')}` 
(`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/ebay/other_listing/synchronization/', 'mode', '{$mode}',
 '0 - disable, \r\n1 - enable', '2015-02-05 00:00:00', '2015-02-05 00:00:00');

SQL
            );
        }

        $tempTable = $installer->getTable('m2epro_ebay_account');

        if ($connection->tableColumnExists($tempTable, 'other_listings_synchronization_mapped_items_mode') !== false) {
            $connection->dropColumn($tempTable, 'other_listings_synchronization_mapped_items_mode');
        }

        // ---------------------------------------

        $date = date('Y-m-d H:i:s', gmdate('U'));

        $installer->run(<<<SQL

  INSERT INTO `{$this->_installer->getTable('m2epro_registry')}`
  SET `key` = 'wizard_migrationToV6_notes_html',
      `value` = (SELECT `data`
                 FROM `m2epro_migration_v6`
                 WHERE `component` = '*'
                 AND `group` = 'notes'),
      `update_date` = '{$date}',
      `create_date` = '{$date}';

  DELETE FROM `m2epro_migration_v6`
  WHERE `component` = '*' AND `group` = 'notes';

SQL
        );
    }

    //########################################
}