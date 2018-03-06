<?php
namespace Evil\Custom\Setup;
/**
 * Class InstallSchema
 *
 * @package Evil\Custom\Setup
 */
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * install tables
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface   $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        // create table evil_custom_options_value
        if (!$installer->tableExists('evil_custom_options_value')) {
            $table = $installer->getConnection()
                ->newTable(
                    $installer->getTable('evil_custom_options_value')
                )
                ->addColumn(
                    'option_type_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Option Type ID'
                )
                ->addColumn(
                    'option_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Option ID'
                )
                ->addColumn(
                    'image1',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Image 1'
                )
                ->addColumn(
                    'image2',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Image 2'
                )
                ->addColumn(
                    'swatch',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Swatch'
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'evil_custom_options_value',
                        'option_id',
                        'catalog_product_option',
                        'option_id'
                    ),
                    'option_id',
                    $installer->getTable('catalog_product_option'),
                    'option_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'evil_custom_options_value_type',
                        'option_type_id',
                        'catalog_product_option_type_value',
                        'option_type_id'
                    ),
                    'option_type_id',
                    $installer->getTable('catalog_product_option_type_value'),
                    'option_type_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                ) 
                ->setComment(
                    'Evil Custom Product Option Details Table'
                );
            $installer->getConnection()
                ->createTable($table);

        }
        // Create Table evil_custom_options
        if (!$installer->tableExists('evil_custom_options')) {
            $table = $installer->getConnection()
                ->newTable(
                    $installer->getTable('evil_custom_options')
                )
                ->addColumn(
                    'option_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Option ID'
                )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Product ID'
                )
                ->addColumn(
                    'headline',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    ['unsigned' => true, 'nullable' => false],
                    'Headline'
                )
                ->addColumn(
                    'description',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '2M',
                    [],
                    'Description'
                )
                ->addColumn(
                    'promo',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Promo'
                )
                ->addColumn(
                    'icon',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Icon'
                )
                ->addColumn(
                    'switch',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    [],
                    'Switch View'
                )
                ->addColumn(
                    'parentable',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    [],
                    'Child Of'
                )
                ->addIndex(
                    $installer->getIdxName('evil_custom_options', ['product_id']),
                    ['product_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                      'evil_custom_options',
                      'option_id',
                      'catalog_product_option',
                      'option_id'),
                    'option_id',
                    $installer->getTable('catalog_product_option'),
                    'option_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment(
                    'Evil Custom Product Options Table'
                );
            $installer->getConnection()
                ->createTable($table);

        }

        $installer->endSetup();
    }
}
