<?php

namespace ShopGo\Paytabs\Model;

class paytabs extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'paytabs';

    protected $_code = self::CODE;

    protected $_countryFactory;
    protected $_helper;
    private   $_gatewayHost = 'https://www.paytabs.com/apiv2/create_pay_page';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \ShopGo\Paytabs\Helper\Data $helper,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Payment\Model\Method\Logger $logger
    ) {
        $this->_countryFactory = $countryFactory;
        $this->_helper         = $helper;
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
        curl_setopt($ch, CURLOPT_URL, $this->_gatewayHost);
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
        }
    }


    public function getPostData($order)
    {

        $gatewayParams = array();

        $merchant_email = "zaina@shopgo.me";

        $access_code = "INk5ZhinxaY7pkPV7fyHc008jsk3YZcHNwMNO5amg8tFtjAAy8hK9iTsKevJdIq0fdN4iXqy6UdzdN9LWrw5JpytD3dQMEQ2RE6a";

        $items = $order->getAllVisibleItems();

        $productsDetails =  $this->productstitle($items);

        $arrBillingAddress = $order->getBillingAddress()->toArray();

        $arrShippingAddress = $order->getShippingAddress()->toArray();

        $gatewayParams["merchant_email"]        = $merchant_email;
        $gatewayParams["secret_key"]            = $access_code;
        $gatewayParams["site_url"]              = "http://paytabs2.devstage.shopgo.io";
        $gatewayParams["return_url"]            = "http://paytabs2.devstage.shopgo.io/paytabs/standard/response/";
        $gatewayParams["title"]                 = 'Title 10202';
        $gatewayParams["cc_first_name"]         = $arrBillingAddress['firstname'];
        $gatewayParams["cc_last_name"]          = $arrBillingAddress['lastname'];
        $gatewayParams["cc_phone_number"]       = "00962";
        $gatewayParams["phone_number"]          = $arrBillingAddress['telephone'];
        $gatewayParams["email"]                 = $arrBillingAddress['email'];
        $gatewayParams["products_per_title"]    = $productsDetails["productTitle"];
        $gatewayParams["unit_price"]            = $productsDetails["productPrice"];
        $gatewayParams["quantity"]              = $productsDetails["productQty"];
        $gatewayParams["other_charges"]         = 5;
        $gatewayParams["amount"]                = $order->getGrandTotal();
        $gatewayParams["discount"]              = 0;
        $gatewayParams["currency"]              = "USD";
        $gatewayParams["reference_no"]          = "ABC_Smaeer";
        $gatewayParams["ip_customer"]           = "212.34.20.88";
        $gatewayParams["ip_merchant"]           = "212.34.20.88";
        $gatewayParams["billing_address"]       = $arrBillingAddress['street'];;
        $gatewayParams["state"]                 = "Amman";
        $gatewayParams["city"]                  = $arrBillingAddress['city'];;
        $gatewayParams["postal_code"]           = $arrBillingAddress['postcode'];;
        $gatewayParams["country"]               = $this->_getISO3Code($arrBillingAddress['country_id']);
        $gatewayParams["shipping_first_name"]   = $arrShippingAddress["firstname"];
        $gatewayParams["shipping_last_name"]    = $arrShippingAddress["lastname"];
        $gatewayParams["address_shipping"]      = $arrShippingAddress["street"];;
        $gatewayParams["city_shipping"]         = $arrShippingAddress["city"];
        $gatewayParams["state_shipping"]        = "Amman";
        $gatewayParams["postal_code_shipping"]  = $arrShippingAddress["postcode"];
        $gatewayParams["country_shipping"]      = $this->_getISO3Code($arrShippingAddress["country_id"]);
        $gatewayParams["msg_lang"]              = "English";
        $gatewayParams["cms_with_version"]      = "Magento 2.0.0";

        return $gatewayParams;

    }
    
    public function getRedirectUrl()
    {
        $url = $this->_helper->getUrl($this->getConfigData('redirect_url'));
        return $url;
    }

    public function getReturnUrl()
    {
        $url = $this->_helper->getUrl($this->getConfigData('return_url'));
        return $url;
    }

    protected function productstitle ($items)
    {
        $products_per_title="";
        $unit_price ="";
        foreach ($items as $item) {
            $product = $item->getProduct();
            $products_per_title = $product->getName()."||";
            $unit_price = $product->getFinalPrice(1)."||";
            $productQty = $item->getQtyOrdered()."||";
        }
        return [ 
            "productTitle" => $products_per_title,
            "productPrice" => $unit_price,
            "productQty"   => $productQty
            ];
    }

    protected function _getISO3Code($countrycode)
    {
        $country = $this->_countryFactory->create()->loadByCode($countrycode, 'iso3_code');

        return $country->getData('iso3_code');
    }
}