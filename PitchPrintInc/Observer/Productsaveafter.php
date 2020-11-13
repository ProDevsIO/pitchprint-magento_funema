<?php
/**
 * PitchPrintInc PitchPrint After Product Save Observer
 *
 * @category    PitchPrintInc
 * @package     PitchPrintInc_PitchPrint
 * @author      PitchPrint - Alcino Van Rooyen
 *
 */
namespace PitchPrintInc\PitchPrint\Observer;

use Magento\Framework\Event\ObserverInterface;
use PitchPrintInc\PitchPrint\Ui\DataProvider\Product\Form\Modifier;
	
class Productsaveafter implements ObserverInterface
{    
	protected $request;
	
	public function __construct(\Magento\Framework\App\RequestInterface $request)
	{
		$this->request = $request;	
	}
	
    public function execute(\Magento\Framework\Event\Observer $observer)
	{		
		$product	= $observer->getProduct();	
		$ppa_pick	= $product->getData(Modifier\PitchPrintDesigns::FIELD_NAME_SELECT);
		
		if ( !empty($ppa_pick) ) {	
			try {	
				$this->saveProductDesign( $product->getId(), $ppa_pick );
			} catch (Exception $e) {
				Mage::log($e->getMessage);
			};
		}
	}
	
	/**
	 * Function to save designId with productId.
	 * 
	 * @params		Int productId, String designId
	 * @return		null
	 * 
	 */
	private function saveProductDesign($prodId, $designId)
	{
		$objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $db             = $resource->getConnection(); 
        $tableName      = $resource->getTableName(\PitchPrintInc\PitchPrint\Config\Constants::TABLE_PRODUCT_DESIGN); //gives table name with prefix
      	$prodId			= $db->quote($prodId);
		$designId		= $db->quote($designId);
		
        $data = $db->query( "REPLACE INTO $tableName VALUES ( $prodId, $designId )" );	
	}
}