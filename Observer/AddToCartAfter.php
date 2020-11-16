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
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\SerializerInterface;

class AddToCartAfter implements ObserverInterface
{
    protected $request;
	protected $serializer;
	protected $allItems;

	public function __construct(
	    RequestInterface $request, 
        SerializerInterface $serializer)
	{
		$this->request = $request;	
		$this->serializer = $serializer;
		$this->allItems = $this->getAllItems();
	}
	
	private function getAllItems() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        return $cart->getQuote()->getAllItems();
	}
	
	private function getProductOccurence($pid) {
	    $occur = 1;
	    foreach($this->allItems as $item)
	        if ($item->getProductId() == $pid) 
	            $occur ++;
	    return $occur;
	}
	
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $ppData = $this->request->getParam('_pitchprint');
        $cItem = $observer->getEvent()->getData('quote_item');
        if ($ppData) {
            $additionalOptions = [];
   	        $additionalOptions[] = [
            	'label' => 'Project',
            	'value' => $this->getProductOccurence($cItem->getProductId())
        	];
            $cItem->addOption(array(
   	            'product_id' => $cItem->getProductId(),
            	'code' => 'additional_options',
            	'value' => $this->serializer->serialize($additionalOptions)
        	));
        }
    }

}
