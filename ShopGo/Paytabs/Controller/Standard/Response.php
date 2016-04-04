<?php

namespace ShopGo\Paytabs\Controller\Standard;

class Response extends \ShopGo\Paytabs\Controller\Paytabs
{

    public function execute()
    {
    	
    	$response = $this->getRequest()->getParams();

		$payment_reference = $response['payment_reference'];


		$merchant_email = $this->_helper->getConfigValue(\ShopGo\Paytabs\Helper\Data::XML_PATH_MEREMAIL);
		$secret_key     = $this->_helper->getConfigValue(\ShopGo\Paytabs\Helper\Data::XML_PATH_SECRET);

		$fields = array(
			'merchant_email' => $merchant_email,
			'secret_key' => $secret_key,
			'payment_reference' => $payment_reference
		);

		$this->_logger->info(print_r($fields,true));

		$fields_string = "";
		foreach ($fields as $key => $value) {
			$fields_string .= $key . '=' . $value . '&';
		}

		rtrim($fields_string, '&');
		$gateway_url = "https://www.paytabs.com/apiv2/verify_payment";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $gateway_url);
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		$ch_result = curl_exec($ch);
		$ch_error = curl_error($ch);
		
		$dec = json_decode($ch_result, true);


		$this->_logger->info(print_r($dec,true));
		
		$orderId = $this->_checkoutSession->getLastRealOrderId();
		$order	 = $this->getOrderById($orderId);
		if ($dec['response_code'] == 100) {
			$order->setStatus($order::STATE_PROCESSING);
			$returnUrl = $this->_helper->getUrl("checkout/onepage/success");
		}
		else
		{
				$order->cancel()->setState($order::STATE_CANCELED, true, 'Rejected Payment');
				$returnUrl =$this->_helper->getUrl("checkout/onepage/failure");
		}

		$order->save();
		$this->getResponse()->setRedirect($returnUrl);   
    }
}
