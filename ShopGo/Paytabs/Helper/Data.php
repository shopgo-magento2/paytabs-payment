<?php

namespace ShopGo\Paytabs\Helper;

use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Module\Dir;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_DEBUG       = 'payment/paytabs/debug';
    const XML_PATH_MEREMAIL    = 'payment/paytabs/username';
    const XML_PATH_SECRET      = 'payment/paytabs/secretkey';
    const PAYTABS              = 'https://www.paytabs.com';

    protected  $Pthost         =  self::PAYTABS;


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Reader $configReader

    )
    {
        parent::__construct($context, $scopeConfig);

    }

    public function getDebugStatus()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEBUG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function checkAccount()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MEREMAIL,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

    }

    public function validateSecretKey() {
        $authentication_URL = $this->Pthost."/apiv2/validate_secret_key";

        $fields = array(
            'merchant_email' => $this->scopeConfig->getValue(self::XML_PATH_MEREMAIL,\Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'secret_key' => $this->scopeConfig->getValue(self::XML_PATH_SECRET,\Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        );
        $fields_string = "";
        foreach($fields as $key=>$value) {
            $fields_string .= urlencode($key).'='.urlencode($value).'&';
        }
        $fields_string = substr($fields_string, 0, strrpos($fields_string, '&'));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$authentication_URL);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $ch_result = curl_exec($ch);
        $ch_error = curl_error($ch);

        $dec = json_decode($ch_result,true);
        return $dec;
    }

}