<?php
/**
 * PitchPrintInc PitchPrint After Product Save Observer Completed
 *
 * @category    PitchPrintInc
 * @package     PitchPrintInc_PitchPrint
 * @author      PitchPrint - Alcino Van Rooyen
 *
 */
 
namespace PitchPrintInc\PitchPrint\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\SerializerInterface;

class AddToCartAfterComplete implements ObserverInterface
{
    protected $request;
	protected $allItems;
	
	public function __construct(
	    RequestInterface $request, 
        SerializerInterface $serializer)
	{
		$this->request = $request;	
		$this->allItems = $this->getAllItems();
		 
	}
	
	private function getAllItems() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        return $cart->getQuote()->getAllItems();
	}
	
	private function saveProjectData($quoteId, $projectData) {
	    $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $db             = $resource->getConnection(); 
        $tableName      = $resource->getTableName(\PitchPrintInc\PitchPrint\Config\Constants::TABLE_QUOTE_ITEM); //gives table name with prefix
      	$quoteId		= $db->quote($quoteId);
		$pData  		= $db->quote($projectData);
		$sql            = "INSERT INTO $tableName VALUES ( $quoteId, $pData )";
        $data           = $db->query( $sql );
        return $data;
	}
	
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $ppData = $this->request->getParam('_pitchprint');
        if ($ppData) {
            $cItem = end($this->allItems);
            if ($cItem->getParentItem()) {
                $cItem = $cItem->getParentItemId();
            }else{
                $cItem = $cItem->getItemId();
            }
            $this->saveProjectData($cItem, $ppData);
        }
    }

}
