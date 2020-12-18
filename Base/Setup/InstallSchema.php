<?php

namespace Zigzag\Base\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $definition = [
            'type'     => Table::TYPE_DATETIME,
            'nullable' => true,
            'comment'  => 'Delivery Date',
        ];

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'zigzag_delivery_from',
            $definition
        )->addColumn(
            $installer->getTable('quote'),
            'zigzag_delivery_to',
            $definition
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'zigzag_delivery_from',
            $definition
        )->addColumn(
            $installer->getTable('sales_order'),
            'zigzag_delivery_to',
            $definition
        );

        $setup->endSetup();
    }
}