<?php

namespace PaySolutions\PsPayments\Controller\Payment;

class Request extends \PaySolutions\PsPayments\Controller\AbstractCheckoutRedirectAction
{
	public function execute() {
		
		// Get current order detail from OrderFactory object.
		$orderId = $this->getCheckoutSession()->getLastRealOrderId();

		if(empty($orderId)) {
			die("Authentication Error: Order is empty.");
		}

		$order = $this->getOrderDetailByOrderId($orderId);

		// Redirect to home page with error
		if(!isset($order)) {
			$this->_redirect('');
			return;
		}

		$customerSession = $this->getCustomerSession();

		$product_name = '';
        foreach($order->getAllItems() as $item) {
            $product_name .= $item->getName() . ', ';
		}
		
		$product_name = (strlen($product_name) > 0) ? substr($product_name, 0, strlen($product_name) - 2) : "";
        $product_name .= '.';
		$product_name = mb_strimwidth($product_name, 0, 255, '...');
		

		// Check whether customer is logged in or not into current merchant website.
		if($customerSession->isLoggedIn()) {
			$customer_email = $customerSession->getCustomer()->getEmail();
		} else {
			$billingAddress = $order->getBillingAddress();
			$customer_email = $billingAddress->getEmail();
		}

		$currency_code = $order->getBaseCurrencyCode();
		switch($currency_code) {
			case 'THB':
				$cc = '00';
				break;
			case 'USD':
				$cc = '01';
				break;	
			case 'JPY':
				$cc = '02';
				break;
			case 'SGD':
				$cc = '03';
				break;
			case 'HKD':
				$cc = '04';
				break;
			case 'EUR':
				$cc = '05';
				break;
			case 'GBP':
				$cc = '06';
				break;
			case 'AUD':
				$cc = '07';
				break;
			case 'CHF':
				$cc = '08';
				break;
			default:
				$cc = '00';
		}

		while(strlen($orderId) < 10) {
			$orderId = '0'. $orderId;
		}

		// Create basic form array.
		$args = array(
			'payment_description'   => $product_name,
			'order_id'              => $orderId,
			'amount'                => round($order->getGrandTotal(), 2),
			'customer_email'        => $customer_email,
			'currency_code'			=> $cc
		);

		$parameter = [];
        foreach ($args as $k => $v) {
            $value = str_replace('&', 'and', $v);
            $parameter[$k] =  $value;
        }

		echo $this->getPsRequest($parameter, $customerSession->isLoggedIn());

		// echo "<pre>";
		// print_r($this->getPsRequest($parameter, $customerSession->isLoggedIn()));
		// die('Test Routing');

	}
}
