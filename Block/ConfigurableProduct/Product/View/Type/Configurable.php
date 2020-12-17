<?php

namespace PitchPrintInc\PitchPrint\Block\ConfigurableProduct\Product\View\Type;

use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\DecoderInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class Configurable
{

    protected $jsonEncoder;
    protected $jsonDecoder;
    protected $stockRegistry;

    public function __construct(
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder,
        StockRegistryInterface $stockRegistry
    ) {

        $this->jsonDecoder = $jsonDecoder;
        $this->jsonEncoder = $jsonEncoder;
        $this->stockRegistry = $stockRegistry;
    }

    // Adding Quantitites (product=>qty)
    public function aroundGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        \Closure $proceed
    ) {
        $quantities = [];
        $config = $proceed();
        $config = $this->jsonDecoder->decode($config);

        foreach ($subject->getAllowProducts() as $product) {
            $stockitem = $this->stockRegistry->getStockItem(
                $product->getId(),
                $product->getStore()->getWebsiteId()
            );
            $quantities[$product->getId()] = $stockitem->getQty();
        }

        $config['custom_entries'] = $quantities;

        return $this->jsonEncoder->encode($config);
    }
}
