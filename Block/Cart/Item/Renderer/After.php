<?php

namespace PitchPrintInc\PitchPrint\Block\Cart\Item\Renderer;

class After extends \Magento\Checkout\Block\Cart\Item\Renderer
{
	public function renderPpProjectId()
	{
		$options 	= $this->getItem()->getOptions();
		$ppId 		= null;

		if (count($options)) {
			if (isset($options[0]['value']) && $data = json_decode($options[0]['value'])) {
				if (isset($data->_pitchprint)) {
					$ppId = $data->_pitchprint;
				}
			}
		}
		return $ppId;
	}

	public function renderPpImage()
	{
		$options 	= $this->getItem()->getOptions();
		$ppId 		= null;

		if (count($options)) {
			if (isset($options[0]['value']) && $data = json_decode($options[0]['value'])) {
				if (isset($data->_pitchprint)) {
					$projectId = json_decode(urldecode($data->_pitchprint))->projectId ?? null;
					$ppId = $projectId;
				}
			}
		}

		return $ppId;
	}

	public function renderPpDesignTitle()
	{
		$options 	= $this->getItem()->getOptions();
		$ppId 		= null;

		if (count($options)) {
			if (isset($options[0]['value']) && $data = json_decode($options[0]['value'])) {
				if (isset($data->_pitchprint)) {
					$designTitle = json_decode(urldecode($data->_pitchprint))->designTitle ?? null;
					$ppId = $designTitle;
				}
			}
		}

		return $ppId;
	}
}
