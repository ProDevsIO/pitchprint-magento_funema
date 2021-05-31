<?php

namespace PitchPrintInc\PitchPrint\Block\Cart\Item\Renderer;

class After extends \Magento\Checkout\Block\Cart\Item\Renderer
{
	public function getCustomAttributeLabel($attributeCode)
	{  	
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
		$eavConfig = $objectManager->get('\Magento\Eav\Model\Config');
		$attribute = $eavConfig->getAttribute('catalog_product', $attributeCode);

		return $attribute->getData("attribute_code");
	}

	public function getCustomAttributeValue($attributeLabel, $optionId)
	{  	
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
		$eavConfig = $objectManager->get('\Magento\Eav\Model\Config');
		$attribute = $eavConfig->getAttribute('catalog_product', $attributeLabel);

		return $attribute->getSource()->getOptionText($optionId);
	}


	public function renderPpProjectId()
	{
		$options 	= $this->getItem()->getOptions();	
		$ppId 		= null;
		
		if (count($options)) {	
			if ( isset($options[0]['value']) && $data = json_decode( $options[0]['value']) ) {
				
				
				if ( isset($data->_pitchprint) ) {
					$ppId = $data->_pitchprint;
				}
			}
		}
	
		return $ppId;
	}

	public function renderCustomAttributes()
	{
		$options 	= $this->getItem()->getOptions();	
		$customOptions = [];
        if (count($options) <= 0) {
			return $customOptions;
		}

		if (isset($options[0]['value']) && $data = json_decode($options[0]['value'])) {			
			if (!isset($data->super_attribute) || gettype($data->super_attribute) !== 'object') {
				return $customOptions;
			}
		}
				
      	foreach ((array) $data->super_attribute as $key => $value) {
			$label = $this->getCustomAttributeLabel($key);
			if ($label) {
				$customOptions[] = [
					'label' => (string) ucwords(str_replace("_"," ",$label)),
					'value' => $this->getCustomAttributeValue($key, $value)
				];
			}
		}
		return $customOptions;
	}

	public function renderPpImage()
	{
		$options 	= $this->getItem()->getOptions();	
		$projectId 		= null;
		
		if (count($options)) {
			if ( isset($options[0]['value']) && $data = json_decode( $options[0]['value']) ) {
				if ( isset($data->_pitchprint) ) {
					$response = json_decode(urldecode($data->_pitchprint))->projectId ?? null;
					$projectId = $response;
				}
			}
		}
		
		return $projectId;
	}

	public function renderPpDesignTitle()
	{
		$options 	= $this->getItem()->getOptions();	
		$designTitle = null;
		
		if (count($options)) {
			if ( isset($options[0]['value']) && $data = json_decode( $options[0]['value']) ) {
				if ( isset($data->_pitchprint) ) {
					$response = json_decode(urldecode($data->_pitchprint))->designTitle ?? null;
					$designTitle = $response;
				}
			}
		}
		
		return $designTitle;
	}
}
