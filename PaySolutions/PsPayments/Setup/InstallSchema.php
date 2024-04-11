<?php
/**
 * Created by PhpStorm.
 * User: nvtro
 * Date: 10/16/2018
 * Time: 3:45 PM
 */

namespace PaySolutions\PsPayments\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        /**
         * Prepare database for install
         */
        $installer->startSetup();

        try {
            // Required tables
            $statusTable = $installer->getTable('sales_order_status');
            $statusStateTable = $installer->getTable('sales_order_status_state');

            // Insert statuses
            $installer->getConnection()->insertArray(
                $statusTable,
                array('status','label'),
                array(array('status' => 'Pending_Payso', 'label' => 'Pending Payso'))
            );

            // Insert states and mapping of statuses to states
            $installer->getConnection()->insertArray(
                $statusStateTable,
                array(
                    'status',
                    'state',
                    'is_default',
                    'visible_on_front'
                ),
                array(
                    array(
                        'status' => 'Pending_Payso',
                        'state' => 'Pending_Payso',
                        'is_default' => 0,
                        'visible_on_front' => 1
                    )
                )
            );
        } catch (Exception $e) {}

        
        $installer->endSetup();
    }
}