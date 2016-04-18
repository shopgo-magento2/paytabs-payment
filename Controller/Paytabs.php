<?php

namespace ShopGo\Paytabs\Controller;

abstract class Paytabs extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \ShopGo\Paytabs\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $_httpClientFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \ShopGo\Paytabs\Helper\Data $helper
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Psr\Log\LoggerInterface $logger,
        \ShopGo\Paytabs\Helper\Data $helper,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
    ) {
        $this->_logger = $logger;
        $this->_helper = $helper;
        $this->_checkoutSession   = $checkoutSession;
        $this->_orderFactory      = $orderFactory;
        $this->_httpClientFactory = $httpClientFactory;
        parent::__construct($context);
    }


    /**
     * Get order object
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrderById($order_id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order_info = $order->loadByIncrementId($order_id);
        return $order_info;
    }

    /**
     * Get order object
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrder()
    {
        return $this->_orderFactory->create()->loadByIncrementId(
            $this->_checkoutSession->getLastRealOrderId()
        );
    }
}