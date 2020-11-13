<?php

namespace PitchPrintInc\PitchPrint\Block\Adminhtml\Order\View\Items\Renderer;

class PitchPrintRenderer extends \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer
{
    
    public function fetchPpData()
    {
		$data = $this->getProjectData($this->getItem()->getQuoteItemId());
		if ($data) return $data[0]['project_data'];
		return 0;
    }
    
    private function getProjectData($quoteId) {
	    $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $db             = $resource->getConnection(); 
        $tableName      = $resource->getTableName(\PitchPrintInc\PitchPrint\Config\Constants::TABLE_QUOTE_ITEM); //gives table name with prefix
		$sql            = "SELECT `project_data` FROM $tableName WHERE `item_id` = $quoteId";
        $data           = $db->fetchAll( $sql );
        return $data;
	}
	
    private function consoleLog($item)
    {
        $item = json_encode($item);
        
        echo "<script>console.log($item);</script>";
    }
}