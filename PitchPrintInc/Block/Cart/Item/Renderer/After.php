<?php

namespace PitchPrintInc\PitchPrint\Block\Cart\Item\Renderer;

class After extends \Magento\Checkout\Block\Cart\Item\Renderer
{
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
}