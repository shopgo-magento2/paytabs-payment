<?php

namespace ShopGo\Paytabs\Model;

class Paytabs extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'paytabs';

    protected $_code = self::CODE;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var \ShopGo\Paytabs\Helper\Data
     */
    protected $_helper;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $_productMetaData;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $_httpClientFactory;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\ServerAddress
     */
    protected $_serverAddress;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \ShopGo\Paytabs\Helper\Data $helper
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ProductMetadata $productMetaData
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Framework\HTTP\PhpEnvironment\ServerAddress $serverAddress
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \ShopGo\Paytabs\Helper\Data $helper,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ProductMetadata $productMetaData,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\HTTP\PhpEnvironment\ServerAddress $serverAddress
    ) {
        $this->_productMetaData   = $productMetaData;
        $this->_countryFactory    = $countryFactory;
        $this->_storeManager      = $storeManager;
        $this->_httpClientFactory = $httpClientFactory;
        $this->_remoteAddress     = $remoteAddress;
        $this->_serverAddress     = $serverAddress;
        $this->_helper            = $helper;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger
        );
    }
     
    /**
     * prepare url payment page
     *
     * @param Order $order
     * @return $string
     */
    public function getPaymentGatewayUrl($order)
    {
        $fields = $this->getPostData($order);

        if ($this->_helper->getDebugStatus()) {
            $this->_logger->info(print_r($fields,true));
        }

        $fields_string = http_build_query($fields);

        $client = $this->_httpClientFactory->create();
        $client->setUri($authentication_URL);
        $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
        $client->setRawData(utf8_encode($fields_string));
        $response= $client->request(\Zend_Http_Client::POST)->getBody();

        $result = json_decode($response,true);

        if ($this->_helper->getDebugStatus()) {
            $this->_logger->info(print_r($result,true));
        }

        if (isset($result['response_code']) && $result['response_code'] == "4012") {
            return $result["payment_url"];
            //$this->_paypageStatus = true;
        } else {
            switch ($result['response_code']) {
                case "4001":
                    $errorMessage = "Variable not found";
                    break;
                case "4002":
                    $errorMessage = "Invalid Credentials";
                    break;
                case "4007":
                    $errorMessage = "Missing parameter";
                    break;
                case "0404":
                    $errorMessage = "You don't have permissions to create an Invoice";
                    break;
                default:
                    $errorMessage = "Something Went Wrong with Payment Information";
                    break;
            }
            //TODO add error message into redirect page
        }
    }

    /**
     * prepare create page request parameters
     *
     * @param Order $order
     * @return $array
     */
    public function getPostData($order)
    {

        $merchant_email = $this->getConfigData('username');;

        $access_code    = $this->getConfigData('secretkey');

        $items = $order->getAllVisibleItems();

        $productsDetails =  $this->productsTitle($items);

        $billingAddress  = $order->getBillingAddress()->toArray();

        $shippingAddress = $order->getShippingAddress()->toArray();

        $params =[
            "merchant_email"        => $merchant_email,
            "secret_key"            => $access_code,
            "site_url"              => $this->_storeManager->getStore()->getBaseUrl(),
            "return_url"            => $this->_storeManager->getStore()->getBaseUrl().$this->getConfigData('return_url'),
            "title"                 => 'Title',//to detect
            "cc_first_name"         => $billingAddress['firstname'],
            "cc_last_name"          => $billingAddress['lastname'],
            "cc_phone_number"       => $this->_helper->_getccPhone($billingAddress['country_id']),
            "phone_number"          => $billingAddress['telephone'],
            "email"                 => $billingAddress['email'],
            "products_per_title"    => $productsDetails["productTitle"],
            "unit_price"            => $productsDetails["productPrice"],
            "quantity"              => $productsDetails["productQty"],
            "other_charges"         => $order->getGrandTotal() - $productsDetails['total'],
            "amount"                => $order->getGrandTotal(),
            "discount"              => 0,//to detect
            "currency"              => $this->_storeManager->getStore()->getCurrentCurrency()->getCode(),
            "reference_no"          => "reference_no",//to detect
            "ip_customer"           => $this->_remoteAddress->getRemoteAddress(),
            "ip_merchant"           => $this->_serverAddress->getServerAddress(),
            "billing_address"       => $billingAddress['street'],
            "state"                 => !empty($billingAddress['region']) ? $billingAddress['region'] : "MENA Country",
            "city"                  => $billingAddress['city'],
            "postal_code"           => $billingAddress['postcode'],
            "country"               => $this->_getISO3Code($billingAddress['country_id']),
            "shipping_first_name"   => $shippingAddress["firstname"],
            "shipping_last_name"    => $shippingAddress["lastname"],
            "address_shipping"      => $shippingAddress["street"],
            "city_shipping"         => $shippingAddress["city"],
            "state_shipping"        => !empty($shippingAddress['region']) ? $shippingAddress['region'] : "MENA Country",
            "postal_code_shipping"  => $shippingAddress["postcode"],
            "country_shipping"      => $this->_getISO3Code($shippingAddress["country_id"]),
            "msg_lang"              => "English",
            "cms_with_version"      => $this->_productMetaData->getVersion()

        ];
        return $params;
    }
    
    /**
     * Get redirect controller url
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        $url = $this->_helper->getUrl($this->getConfigData('redirect_url'));
        return $url;
    }

    /**
     * Get response controller url
     *
     * @return string
     */
    public function getReturnUrl()
    {
        $url = $this->_helper->getUrl($this->getConfigData('return_url'));
        return $url;
    }


    /**
     * return array of products with details as paytabs api required 
     *
     * @param Collection $items
     * @return array
     */
    protected function productsTitle ($items)
    {
        $products_per_title="";
        $unit_price ="";
        $total=0;
        $productQty="";
        foreach ($items as $item) {
            $product = $item->getProduct();
            $products_per_title .= $product->getName()." || ";
            $unit_price .= $product->getFinalPrice(1)." || ";
            $productQty .= $item->getQtyOrdered()." || ";
            $total     += $product->getFinalPrice(1) * $item->getQtyOrdered();
        }
        return [ 
            "productTitle" => $products_per_title,
            "productPrice" => $unit_price,
            "productQty"   => $productQty,
            "total"        => $total
            ];
    }

    /**
     * get country code with ISO3 fromat 
     *
     * @param string $countrycode
     * @return string
     */
    protected function _getISO3Code($countrycode)
    {
        $country = $this->_countryFactory->create()->loadByCode($countrycode, 'iso3_code');

        return $country->getData('iso3_code');
    }
}