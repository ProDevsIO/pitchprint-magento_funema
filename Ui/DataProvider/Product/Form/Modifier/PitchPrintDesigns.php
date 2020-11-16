<?php

/*
 * Reference for this code template:
 *	https://blog.mageworx.com/2016/08/an-easy-way-to-add-a-fieldset-with-fields-to-the-ui-form/
 *
 */

namespace PitchPrintInc\PitchPrint\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;

use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;


class PitchPrintDesigns extends AbstractModifier
{

    // Components indexes
    const CUSTOM_FIELDSET_INDEX = 'pitch_print_fieldset';
    const CUSTOM_FIELDSET_CONTENT = 'pitch_print_fieldset_content';
    const CONTAINER_HEADER_NAME = 'pitch_print_fieldset_content_header';

    // Fields names
//     const FIELD_NAME_TEXT = 'example_text_field';
    const FIELD_NAME_SELECT = 'ppa_pick';
//     const FIELD_NAME_MULTISELECT = 'example_multiselect_field';

	const PP_FIRST_SELECT_ELEMENT = [ [ 'value' => 'none', 'label' => 'None' ] ];

    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var array
     */
    protected $meta = [];

	/**
	 * @var object : database instance.
	 */
	protected $_db;
	
	/**
	 * @var object : resource connection.
	 */
	protected $_resource;
	
	/**
	 * @var text
	 */
	protected $_prodDesignId;
	
    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder;
		
		$objectManager  	= \Magento\Framework\App\ObjectManager::getInstance();
       	$this->_resource    = $objectManager->get('Magento\Framework\App\ResourceConnection');
    	$this->_db      	= $this->_resource->getConnection();
		
		$this->_prodDesignId = $this->_getProductDesign();
    }
	
	/**
	 * Fetch A design Id that is already associated with this product.
	 */
	private function _getProductDesign()
	{
		$tableName 		= $this->_resource->getTableName(\PitchPrintInc\PitchPrint\Config\Constants::TABLE_PRODUCT_DESIGN);
		$product_id 	= $this->locator->getProduct()->getId();
		
		if ($product_id) {
			$design_id		= $this->_db->fetchAll( "SELECT `design_id` FROM $tableName WHERE `product_id` = $product_id" );

			if ( count($design_id) ) {
				return $design_id[0]['design_id'];
			}
		}
		return 0;
	}

    /**
     * Data modifier, does nothing in our example.
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Meta-data modifier: adds ours fieldset
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $this->addCustomFieldset();

        return $this->meta;
    }

    /**
     * Merge existing meta-data with our meta-data (do not overwrite it!)
     *
     * @return void
     */
    protected function addCustomFieldset()
    {
        $this->meta = array_merge_recursive(
            $this->meta,
            [
                static::CUSTOM_FIELDSET_INDEX => $this->getFieldsetConfig(),
            ]
        );
    }

    /**
     * Declare ours fieldset config
     *
     * @return array
     */
    protected function getFieldsetConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('PitchPrint'),
                        'componentType' => Fieldset::NAME,
                        'dataScope' => static::DATA_SCOPE_PRODUCT, // save data in the product data
                        'provider' => static::DATA_SCOPE_PRODUCT . '_data_source',
                        'ns' => static::FORM_NAME,
                        'collapsible' => true,
                        'sortOrder' => 10,
                        'opened' => true,
                    ],
                ],
            ],
            'children' => [
                static::CONTAINER_HEADER_NAME => $this->getHeaderContainerConfig(10),
                static::FIELD_NAME_SELECT => $this->getSelectFieldConfig(30)
            ],
        ];
    }

    /**
     * Get config for header container
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getHeaderContainerConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => null,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => $sortOrder,
                        'content' => __('Select the design you wish to associate with this product.'),
                    ],
                ],
            ],
            'children' => [],
        ];
    }

    /**
     * Example select field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getSelectFieldConfig($sortOrder)
    {
// 		print_r($this->_prodDesignId);die();
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Choose a design'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataScope' => static::FIELD_NAME_SELECT,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'options' => $this->getPpDesigns(),
                        'visible' => true,
                        'disabled' => false,
						'value' => $this->_prodDesignId
                    ],
                ],
            ],
        ];
    }

	/**
	 * Single Design Option build here
	 *
	 * @param $item
	 * @return array
	 */
	private function createSingleItem ($item) {
		return [ 'value' => $item->id, 'label' => '&nbsp; &nbsp; &nbsp; » ' . $item->title, $item ]; 
	}
	
	/**
	 * Each Category's list of designs are built here
	 *
	 * @param $item
	 * @param &$options
	 * @return modifies &$options
	 */
	private function createListItems ( $items, &$options ) { 
		foreach ( $items as $design ) {
			if ( isset($design->title) ) {				
				$options[] = $this->createSingleItem($design);
			}else{
				/* Need to fix Designs without titles */
			}
		}
	}
	
	/**
	 * Build Category and it's design into options for
	 * Select Component.
	 *
	 * @param $designs
	 * @return array $options
	 */
    private function createList( $designs )
    {
    	$options = static::PP_FIRST_SELECT_ELEMENT;
		
        foreach( $designs as $data ) {		
			$options[] = [ 'value' => '*'.$data->id, 'label' => $data->title ];
        	
			if ( isset( $data->items ) && count( $data->items ) ) {
				$this->createListItems( $data->items, $options );
			}
        }
        return $options;
    }
    
	/**
	 * Fetches the categories and designs from PitchPrint.
	 *
	 * @param $cridentials
	 * return array
	 */
    private function fetchPpDesigns( $credentials )
    {
        define('PITCH_APIKEY', $credentials['api_key']);
        define('PITCH_SECRETKEY', $credentials['secret_key']);

        function generateSignature () {
            $timestamp = time();
            $signature = md5(PITCH_APIKEY . PITCH_SECRETKEY . $timestamp);
            return array ('timestamp'=>$timestamp, 'apiKey'=>PITCH_APIKEY, 'signature'=>$signature);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.pitchprint.io/runtime/fetch-designs");
        curl_setopt($ch, CURLOPT_POST, true);

        $opts = generateSignature();

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($opts));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $output  = curl_exec($ch);
        $designs = json_decode($output);
		
        if ( isset($designs->data) && count($designs->data) ) {
            return $this->createList($designs->data);
        }

        return static::PP_FIRST_SELECT_ELEMENT;
    }
    
	/**
	 * Retrieves stored API and Secret Key, then queries PitchPrint for designs with them.
	 *
	 * @return array
	 */
    private function getPpDesigns()
    {
        $tableName      = $this->_resource->getTableName(\PitchPrintInc\PitchPrint\Config\Constants::TABLE_CONFIG);      
        $credentials	= $this->_db->fetchAll("SELECT * FROM $tableName");
        
        if ( isset( $credentials[0] ) )
        	return $this->fetchPpDesigns( $credentials[0] );
        return static::PP_FIRST_SELECT_ELEMENT;
    }
}
