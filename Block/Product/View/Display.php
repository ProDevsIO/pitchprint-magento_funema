<?php

namespace PitchPrintInc\PitchPrint\Block\Product\View;

class Display extends \Magento\Framework\View\Element\Template {


	protected $_productId;
	protected $_resource;
	protected $_db;
	protected $_design_id;
	protected $_api_key;
	protected $_product_designs;
	protected $_customer;
	protected $_productName;

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Customer\Model\Session $customer
	) {
		 parent::__construct( $context );

		$objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
		$this->_resource = $objectManager->get( 'Magento\Framework\App\ResourceConnection' );
		$this->_db       = $this->_resource->getConnection();

		$this->_productId       = $coreRegistry->registry( 'current_product' )->getId();
		$this->_productName     = $coreRegistry->registry( 'current_product' )->getName();
		$this->_design_id       = $this->_fetchPpDesignId( $this->_productId );
		$this->_api_key         = $this->_fetchPpApiKey();
		$this->_product_designs = $this->_fetchProductDesigns();
		$this->_customer        = $customer;
	}

	private function _fetchPpDesignId( $product_id ) {
		$tableName = $this->_resource->getTableName( \PitchPrintInc\PitchPrint\Config\Constants::TABLE_PRODUCT_DESIGN );
		$design_id = $this->_db->fetchAll( "SELECT `design_id` FROM $tableName WHERE `product_id` = $product_id" );

		if ( count( $design_id ) ) {
			return $design_id[0]['design_id'];
		}
		return 0;
	}
	private function _fetchProductDesigns() {
		$tableName      = $this->_resource->getTableName( \PitchPrintInc\PitchPrint\Config\Constants::TABLE_PRODUCT_DESIGN );
		$productDesigns = $this->_db->fetchAll( "SELECT * FROM $tableName" );

		if ( count( $productDesigns ) ) {
			return $productDesigns;
		}
		return array();
	}

	private function _fetchPpApiKey() {
		 $tableName = $this->_resource->getTableName( \PitchPrintInc\PitchPrint\Config\Constants::TABLE_CONFIG );
		$api_key    = $this->_db->fetchAll( "SELECT `api_key` FROM $tableName" );

		if ( count( $api_key ) ) {
			return $api_key[0]['api_key'];
		}
		return 0;
	}

	public function getDesignId() {
		return $this->_design_id; }

	public function getApiKey() {
		return $this->_api_key; }

	public function getProductId() {
		return $this->_productId; }

	public function getAllPitchPrintProductDesigns() {
		return $this->_product_designs; }

	public function getProductName() {
		return $this->_productName ?? ''; }

	public function getUserId() {
		if ( ! $this->_customer->isLoggedIn() ) {
			return '';
		}
		return $this->_customer->getCustomer()->getId();
	}
	public function getUserData() {
		$customer = $this->_customer->getCustomer();
		if ( ! $this->_customer->isLoggedIn() ) {
			return json_encode(
				array(
					'id'        => '',
					'email'     => '',
					'name'      => '',
					'firstname' => '',
					'lastname'  => '',
					'telephone' => '',
					'address'   => '',
				)
			);
		}

		$id        = $customer->getId();
		$name      = $customer->getName();
		$firstname = $customer->getFirstname();
		$lastname  = $customer->getLastname();
		$telephone = '';
		$email     = $customer->getEmail();
		$address   = ! empty( $customer->getAddressById( $customer->getDefaultShipping() )->getStreet() ) ?
					implode( ',', $customer->getAddressById( $customer->getDefaultShipping() )->getStreet() ) : 'No address';

		$payload = json_encode(
			array(
				'id'        => $id,
				'email'     => $email,
				'name'      => $name,
				'firstname' => $firstname,
				'lastname'  => $lastname,
				'telephone' => $telephone,
				'address'   => $address,
			)
		);

		return $payload;
	}
}
