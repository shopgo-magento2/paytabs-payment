<?php

namespace ShopGo\Paytabs\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class paytabsConfigProvider implements ConfigProviderInterface
{
    protected $methodCode = \ShopGo\Paytabs\Model\Paytabs::CODE;

    protected $method;

    protected $escaper;



    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->escaper = $escaper;
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
    }


    public function getConfig()
    {
        return $this->method->isAvailable() ? [
            'payment' => [
                'paytabs' => [
                    'redirectUrl' => $this->getRedirectUrl()
                ]
            ]
        ] : [];
    }

    protected function getRedirectUrl()
    {
        return $this->method->getRedirectUrl();
    }
}