<?php

namespace ShopGo\Paytabs\Controller\Standard;

class Redirect extends \Magento\Framework\App\Action\Action
{
	
	/** @var \Magento\Framework\View\Result\PageFactory  */
    protected $resultPageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    
    public function execute()
    {   
        $paymentMethod = $this->_objectManager->create('ShopGo\Paytabs\Model\paytabs');

        $data = $this->getRequest()->getPostValue();

        return $this->resultPageFactory->create();
    }
}
