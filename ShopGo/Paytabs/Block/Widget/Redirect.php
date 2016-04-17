<?php

namespace ShopGo\Paytabs\Block\Widget;


use \Magento\Framework\View\Element\Template;


class Redirect extends Template
{
    protected $_paytabs;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \ShopGo\Paytabs\Model\Paytabs $paytabs,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_paytabs = $paytabs;
    }

    /**
     * Get payment page url
     *
     * @return null|string
     */
    public function getPaymentGatewayUrl()
    {
        return $this->_paytabs->getPaymentGatewayUrl($this->getOrder());
    }

    /**
     * Get order object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_orderFactory->create()->loadByIncrementId(
            $this->_checkoutSession->getLastRealOrderId()
        );
    }
}