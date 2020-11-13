<?php

namespace PitchPrintInc\PitchPrint\Controller\Adminhtml\Configuration;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Save extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    protected $keysModel;
    
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Csrf stuff
     * José H Van Amson contributed these two functions below
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $request = $this->getRequest()->getParams();

        try{
            $this->saveCredentials($request);
            $this->resultPageFactory->create();
            $this->messageManager->addSuccessMessage(__('PitchPrint Credentials Updated.'));
            return $resultRedirect->setPath('pitchprint/configuration/index');
        }catch (\Exception $e){
            print_r($e);die();
            $this->messageManager->addExceptionMessage($e, __('We can\'t submit your request, Please try again.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return $resultRedirect->setPath('pitchprint/configuration/index');
        }

    }
  
    private function saveCredentials($request)
    {
        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $db             = $resource->getConnection();

        $api_key    = $db->quote( trim($request['api_key'], ' ') );
        $secret_key = $db->quote( trim($request['secret_key'], ' ') );
      
        $tableName      = $resource->getTableName(\PitchPrintInc\PitchPrint\Config\Constants::TABLE_CONFIG); //gives table name with prefix
      
        $data = $db->query("REPLACE INTO $tableName VALUES (1, $api_key, $secret_key)");
    }

}
?>