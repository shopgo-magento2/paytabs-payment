<?php

namespace ShopGo\Paytabs\Model;

class paytabs extends \Magento\Payment\Model\Method\AbstractMethod
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
        \Magento\Framework\App\ProductMetadata $productMetaData
    ) {
        $this->_productMetaData= $productMetaData;
        $this->_countryFactory = $countryFactory;
        $this->_helper         = $helper;
        $this->_storeManager=$storeManager;
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

        $this->_logger->info(print_r($fields,true));

        $fields_string = "";
        foreach ($fields as $key => $value) {
            $fields_string .= urlencode($key) . '=' . urlencode($value) . '&';
        }
        $fields_string = substr($fields_string, 0, strrpos($fields_string, '&'));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \ShopGo\Paytabs\Helper\Data::PAYTABS_SITE.\ShopGo\Paytabs\Helper\Data::CREATE_PAY_PAGE);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $ch_result = curl_exec($ch);
        $ch_error = curl_error($ch);

        $dec = json_decode($ch_result, true);

        $this->_logger->info(print_r($dec,true));

        if (isset($dec['response_code']) && $dec['response_code'] == "4012") {
            return $dec["payment_url"];
            //$this->_paypageStatus = true;
        } else {
            switch ($dec['response_code']) {
                case "4001":
                    $errorMessage = "Variable not found.";
                    break;
                case "4002":
                    $errorMessage = "Invalid Credentials.";
                    break;
                case "4007":
                    $errorMessage = "Missing parameter.";
                    break;
                case "0404":
                    $errorMessage = "You don't have permissions to create an Invoice.";
                    break;
                default:
                    $errorMessage = "Something Went Wrong with Payment Information";
                    break;
            }
            //to add error message into redirect page
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
            "ip_customer"           => $_SERVER['SERVER_ADDR'],//to detect
            "ip_merchant"           => $_SERVER['SERVER_ADDR'],//to detect
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
     * @return $string
     */
    public function getRedirectUrl()
    {
        $url = $this->_helper->getUrl($this->getConfigData('redirect_url'));
        return $url;
    }

    /**
     * Get response controller url
     *
     * @return $string
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
     * @return $array
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
     * @param String $countrycode
     * @return String
     */
    protected function _getISO3Code($countrycode)
    {
        $country = $this->_countryFactory->create()->loadByCode($countrycode, 'iso3_code');

        return $country->getData('iso3_code');
    }
}