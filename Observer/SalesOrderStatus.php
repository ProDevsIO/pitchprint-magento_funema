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
    protected $logger;
    protected $_storeManager;

    public function __construct(
        Session $authSession,
        CountryFactory $countryFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->authSession = $authSession;
        $this->countryFactory = $countryFactory;
        $this->logger = $logger;
        $this->_storeManager = $storeManager;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->logger->info('New order');

        if ($order->getStatus() == Order::STATE_COMPLETE) {
            $user = $this->authSession->getUser();
            $userId = $user ? $user->getId() : 0;
            $items          = $order->getAllItems();
            $pp_items       = array();

            foreach ($items as $item) {
                $pp_data = $this->fetchPpData($item->getQuoteItemId());

                if (!$pp_data) {
                    continue;
                }

                $projectData = json_decode(urldecode($pp_data));
                $designTitle = $projectData->designTitle;

                $metaData = (object) [
                    "id" => null,
                    "qty" =>  $item->getProduct()->getAttributeText('frame_quantity') ?? $item->getQtyOrdered(),
                    "designTitle" => $designTitle,
                    'storeName' => $this->getStoreCode()
                ];

                $newItem = [];
                $newItem['name']        = $item->getName();
                $newItem['id']          = null;
                $newItem['qty']         = json_encode($metaData);
                $newItem['pitchprint']  = $pp_data;
                array_push($pp_items, $newItem);
            }
            if (!count($pp_items)) {
                return;
            }
            $getCredentials = $this->ppGetCreds();
            if (!isset($getCredentials[0])) {
                return;
            }
            $credentials = $this->generateSignature($getCredentials[0]);
            $order_details = $this->setOrderDetails($order, $userId, $pp_items, $credentials);
            if ($order_details) {
                $this->logger->info('Sending webhook');
                $this->logger->info('Order', (array) $order_details);
                $this->sendWebhook($order_details);
            }
        }
    }

    private function sendWebhook($opts)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.pitchprint.io/runtime/order-complete");
        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($opts));
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array (
                'Accept: application/json',
                'Content-Type: application/json'
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $output  = curl_exec($ch);
        curl_close($ch);
    }

    private function setOrderDetails($order, $userId, $p_items, $cred)
    {

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $billingAddressArray = [];
        $shippingAddressArray = [];

        if ($billingAddress) {
            if (!is_null($billingAddress->getStreet()) && !empty($billingAddress->getStreet())) {
                array_push($billingAddressArray, $billingAddress->getStreet());
            }
            if (!is_null($billingAddress->getCity())) {
                array_push($billingAddressArray, $billingAddress->getCity());
            }
            if (!is_null($billingAddress->getRegion())) {
                array_push($billingAddressArray, $billingAddress->getRegion());
            }
            if (!is_null($this->countryFactory->create()->loadByCode($billingAddress->getCountryId())->getName())) {
                array_push(
                    $billingAddressArray,
                    $this->countryFactory->create()->loadByCode($billingAddress->getCountryId())->getName()
                );
            }
        }

        if ($shippingAddress) {
            if (!is_null($shippingAddress->getStreet())) {
                array_push($shippingAddressArray, $shippingAddress->getStreet());
            }
            if (!is_null($shippingAddress->getCity())) {
                array_push($shippingAddressArray, $shippingAddress->getCity());
            }
            if (!is_null($shippingAddress->getRegion())) {
                array_push($shippingAddressArray, $shippingAddress->getRegion());
            }
            if (!is_null($this->countryFactory->create()->loadByCode($shippingAddress->getCountryId())->getName())) {
                array_push(
                    $shippingAddressArray,
                    $this->countryFactory->create()->loadByCode($shippingAddress->getCountryId())->getName()
                );
            }
        }
        return array (
                'products' =>  urlencode(json_encode($p_items)),
                'client' => 'mg',
                'billingEmail' => $order->getCustomerEmail(),
                'billingPhone' => $order->getShippingAddress() ? $order->getShippingAddress()->getTelephone() : "",
                'billingName' => $order->getCustomerName(),
                'billingAddress' => $billingAddressArray,
                'shippingName' => $order->getShippingAddress() ? $order->getShippingAddress()->getFirstName() : "",
                'shippingAddress' => $shippingAddressArray,
                'orderId' => $order->getIncrementId(),
                'customer' => $userId,
                'status' => 'new',
                'apiKey' => $cred['apiKey'],
                'signature' => $cred['signature'],
                'timestamp' => $cred['timestamp']
        );
    }

    private function fetchPpData($quoteId)
    {
        $data = $this->getProjectData($quoteId);
        if ($data) {
            return $data[0]['project_data'];
        }
        return 0;
    }

    private function getProjectData($quoteId)
    {
        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $db             = $resource->getConnection();
        $tableName      = $resource->getTableName(\PitchPrintInc\PitchPrint\Config\Constants::TABLE_QUOTE_ITEM);
        $sql            = "SELECT `project_data` FROM $tableName WHERE `item_id` = $quoteId";
        return $db->fetchAll($sql);
    }

    private function generateSignature($credentials)
    {
        $timestamp = time();
        $signature = md5($credentials['api_key'] . $credentials['secret_key'] . $timestamp);
        return array ('timestamp' => $timestamp, 'apiKey' => $credentials['api_key'], 'signature' => $signature);
    }

    private function ppGetCreds()
    {
        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $db             = $resource->getConnection();
        $tableName      = $resource->getTableName(\PitchPrintInc\PitchPrint\Config\Constants::TABLE_CONFIG);

        return $db->fetchAll("SELECT * FROM $tableName");
    }

    /**
     * Get Store name
     *
     * @return string
     */
    private function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }
}
