<?php

namespace ShopGo\Paytabs\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class paytabsConfigProvider implements ConfigProviderInterface
{
    protected $methodCode = \ShopGo\Paytabs\Model\paytabs::CODE;

    protected $method;

    protected $escaper;

    public function __construct(
        Magento\Payment\Helper\Data $Helper
    ) {
        $this->method = $Helper->getMethodInstance(ShopGo\Paytabs\Model\paytabs::CODE);
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