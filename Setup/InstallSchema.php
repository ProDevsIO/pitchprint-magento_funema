<?php

namespace PitchPrintInc\PitchPrint\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    protected $_installer;
    protected $_conn;
    
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->_installer = $setup;
        $this->_installer->startSetup();
        
        $this->_conn  = $this->_installer->getConnection();

        // Get pitchprint config table.
        $tableName = $this->_installer->getTable('pitch_print_config');
        $this->_createTable( $tableName, [
            [
            	'name'		=> 'id',
                'type'		=> Table::TYPE_INTEGER,
                'primary'	=> true
            ],
            [ 
                'name'  => 'api_key',
                'type'  => Table::TYPE_TEXT
            ],
            [
                'name' => 'secret_key',
                'type' => Table::TYPE_TEXT
            ]
        ]);
        
        // Get pitchprint product design table.
        $tableName = $this->_installer->getTable('pitch_print_product_design');
        $this->_createTable( $tableName, [
            [
                'name' => 'product_id',
                'type' => Table::TYPE_INTEGER,
                'primary' => true
            ],
            [
                'name' => 'design_id',
                'type' => Table::TYPE_TEXT
            ]
        ]);
        
        // Get PitchPrint quote item table.
        $tableName = $this->_installer->getTable('pitch_print_quote_item');
        $this->_createTable( $tableName, [
            [
                'name' => 'item_id',
                'type' => Table::TYPE_INTEGER
            ],
            [
                'name' => 'project_data',
                'type' => Table::TYPE_TEXT
            ]
        ]);
        $this->_installer->endSetup();
    }
    
    private function _createTable( $tableName, $columns )
    {
        // Check if the table already exists
        if ( !$this->_conn->isTableExists($tableName) ) {

            // Create the table
            $table = $this->_installer->getConnection()->newTable($tableName);

            foreach ($columns as $key => $column) {

                $table->addColumn(
                    $column['name'],
                    $column['type'],
                    null, 
                    [ 
                        'identity' => ( isset($column['primary']) ),
                        'primary' => ( isset($column['primary']) ),
                        'nullable' => false
                    ],
                    $this->cleanName($column['name'])
                );

            }
            
            $table->setComment('Store PitchPrint Crendentials')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            
            $this->_installer->getConnection()->createTable($table);
        }

    }

    private function cleanName ($name) { return ucwords( str_replace( '_', ' ', $name ) ); }

}
