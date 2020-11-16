<?php

namespace PitchPrintInc\PitchPrint\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Directory\Model\CountryFactory;
use Magento\Sales\Model\Order;


class SalesOrderStatus implements ObserverInterface
{  
    protected $authSession;
    protected $countryFactory;
    
    public function __construct(
        Session $authSession,
        CountryFactory $countryFactory
    )
    {
        $this->authSession = $authSession;
        $this->countryFactory = $countryFactory;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
      
        if($order->getState() == Order::STATE_COMPLETE) {
            $user = $this->authSession->getUser();
            $userId = $user ? $user->getId() : 0;
            $items          = $order->getAllItems();
            $pp_items       = array();
            
            foreach($items as $item) {
                $pp_data = $this->fetchPpData($item->getQuoteItemId());
                if (!$pp_data) continue;
                $newItem = [];
                $newItem['name']        = $item->getName();
                $newItem['id']          = null;
                $newItem['qty']         = $item->getQtyOrdered();
                $newItem['pitchprint']  = $pp_data;
                array_push($pp_items, $newItem);
            }
            
            if (!count($pp_items)) return;
            $creds = $this->ppGetCreds();
            if (!isset($creds[0])) return;
            $credentials = $this->generateSignature($creds[0]);
            $order_details = $this->setOrderDetails($order, $userId, $pp_items, $credentials);
            if ($order_details) $this->sendWebhook($order_details);
        }
    }
    private function sendWebhook ($opts) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.pitchprint.io/runtime/order-complete");
        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($opts));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $output  = curl_exec($ch);
    }
    private function setOrderDetails($order, $userId, $p_items, $cred)
    {
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        
        $billingAddressArray = [];
        array_push($billingAddressArray, $billingAddress->getStreet());
        array_push($billingAddressArray, $billingAddress->getCity());
        array_push($billingAddressArray, $billingAddress->getRegion());
        array_push($billingAddressArray, $this->countryFactory->create()->loadByCode($billingAddress->getCountryId())->getName());
        
        $shippingAddressArray = [];
        array_push($shippingAddressArray, $billingAddress->getStreet());
        array_push($shippingAddressArray, $billingAddress->getCity());
        array_push($shippingAddressArray, $billingAddress->getRegion());
        array_push($shippingAddressArray, $this->countryFactory->create()->loadByCode($shippingAddress->getCountryId())->getName());
        
        $opts =  array (
                'products' =>  urlencode(json_encode($p_items)),
                'client' => 'mg',
                'billingEmail' => $order->getCustomerEmail(),
                'billingPhone' => $order->getShippingAddress()->getTelephone(),
                'billingName' => $order->getCustomerName(),
                'billingAddress' => $billingAddressArray,
                'shippingName' => $order->getShippingAddress()->getFirstName(),
                'shippingAddress' => $shippingAddressArray,
                'orderId' => $order->getId(),
                'customer' => $userId,
                'status' => 'new',
                'apiKey' => $cred['apiKey'],
                'signature' => $cred['signature'],
                'timestamp' => $cred['timestamp']
        );
        return $opts;
    }
    private function fetchPpData($quoteId)
    {
        $data = $this->getProjectData($quoteId);
        if ($data) return $data[0]['project_data'];
        return 0;
    }
    private function getProjectData($quoteId) {
        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $db             = $resource->getConnection(); 
        $tableName      = $resource->getTableName(\PitchPrintInc\PitchPrint\Config\Constants::TABLE_QUOTE_ITEM);
        $sql            = "SELECT `project_data` FROM $tableName WHERE `item_id` = $quoteId";
        $data           = $db->fetchAll( $sql );
        return $data;
    }
    private function generateSignature ( $credentials ) {
        $timestamp = time();
        $signature = md5($credentials['api_key'] . $credentials['secret_key'] . $timestamp);
        return array ('timestamp'=>$timestamp, 'apiKey'=>$credentials['api_key'], 'signature'=>$signature);
    }
    private function ppGetCreds()
    {
        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $db             = $resource->getConnection();
        $tableName      = $resource->getTableName(\PitchPrintInc\PitchPrint\Config\Constants::TABLE_CONFIG);
        
        return $db->fetchAll("SELECT * FROM $tableName");
    }
}