<?php
namespace PitchPrintInc\PitchPrint\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class AdditionalConfigProvider implements ConfigProviderInterface
{

    public function __construct(\Magento\Checkout\Model\Cart $cart)
    {
		$this->cart = $cart;
    }
    
    public function getConfig()
    {
        $config 	= [];
		$cartItems 	= $this->cart->getItems();		
		$ppIds 		= [];
		
		foreach ($cartItems as $item) { 
			$ppIds[] = $this->getPpProjectId($item->getOptions());
        }
		
		$config['_pitchprint'] = $ppIds;
        return $config;
    }
    
    protected function getPpProjectId($options)
    {
       	if (count($options)) {
			if ( isset($options[0]['value']) && $data = json_decode( $options[0]['value']) ) {
				if ( isset($data->_pitchprint) ) {
					return $data->_pitchprint;
				}
			}
		}
        return null;
    }
}