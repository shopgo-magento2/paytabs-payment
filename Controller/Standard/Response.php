<?php

namespace ShopGo\Paytabs\Controller\Standard;

class Response extends \ShopGo\Paytabs\Controller\Paytabs
{

    public function execute()
    {
   
        $response          = $this->getRequest()->getParams();
        $payment_reference = $response['payment_reference'];

        $merchant_email    = $this->_helper->getConfigValue(\ShopGo\Paytabs\Helper\Data::XML_PATH_MEREMAIL);
        $secret_key        = $this->_helper->getConfigValue(\ShopGo\Paytabs\Helper\Data::XML_PATH_SECRET);

        $fields = array(
            'merchant_email'    => $merchant_email,
		    'secret_key'        => $secret_key,
            'payment_reference' => $payment_reference
        );

        if ($this->_helper->getDebugStatus()) {
		    $this->_logger->info(print_r($fields,true));
        }

        $fields_string = http_build_query($fields);

        $gateway_url = \ShopGo\Paytabs\Helper\Data::PAYTABS_SITE.\ShopGo\Paytabs\Helper\Data::VERFY_PAYMENT;

        $client = $this->_httpClientFactory->create();
        $client->setUri($gateway_url);
        $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
        $client->setRawData(utf8_encode($fields_string));
        $response= $client->request(\Zend_Http_Client::POST)->getBody();

        $result = json_decode($response,true);

        if ($this->_helper->getDebugStatus()) {
            $this->_logger->info(print_r($result,true));
        }

        $orderId = $this->_checkoutSession->getLastRealOrderId();
        $order	 = $this->getOrderById($orderId);

        if ($result['response_code'] == 100) {

            $order->setStatus($this->_helper->getConfigValue(\ShopGo\Paytabs\Helper\Data::XML_PATH_STATUS));
            $returnUrl = $this->_helper->getUrl("checkout/onepage/success");

        }else{

            $order->cancel()->setState($order::STATE_CANCELED, true, 'Rejected Payment');
            $returnUrl =$this->_helper->getUrl("checkout/onepage/failure");
        }

        $order->save();
        $this->getResponse()->setRedirect($returnUrl);
    }
}
