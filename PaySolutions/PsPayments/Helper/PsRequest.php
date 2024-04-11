<?php

namespace PaySolutions\PsPayments\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\Form\FormKey;

class PsRequest extends AbstractHelper 
{
    private $objConfigSettings;
    private $objStoreManagerInterface;
    protected $formKey;
    protected $_logger;

    function __construct(
        ScopeConfigInterface $configSettings,
        StoreManagerInterface $storeManagerInterface,
        FormKey $formKey,
	\Psr\Log\LoggerInterface $logger
    ) {
        $this->objConfigSettings        = $configSettings->getValue('payment/payso_payment');
        $this->objStoreManagerInterface = $storeManagerInterface;
        $this->formKey = $formKey;
	$this->_logger 			 = $logger;

    }
    
    // Declare the Form array to hold the form request.
	private $arrayPaysoFormFields = array(
        "merchantid"     => "",
        "refno"          => "",
        "customeremail"  => "",
        "productdetail"  => "",
        "total"          => "",
        "lang"			 => "",
        "cc"             => ""
    );

    public function getFormKey() {
        return $this->formKey->getFormKey();
    }
    
    private function generatePaysoCommonFormFields($parameter) {             
        $this->arrayPaysoFormFields["merchantid"] 	 = $this->objConfigSettings['merchant_id'];
        $this->arrayPaysoFormFields["refno"] 	     = $parameter['order_id']; // 99999;
        $this->arrayPaysoFormFields["customeremail"] = $parameter['customer_email'];
        $this->arrayPaysoFormFields["productdetail"] = $parameter['payment_description'];
        $this->arrayPaysoFormFields["total"]         = $parameter['amount'];
        $this->arrayPaysoFormFields["lang"] 	     = $this->objConfigSettings['lang'];
        $this->arrayPaysoFormFields["cc"]            = $parameter['currency_code'];
    }
    
    // Get the merchant website return URL.
	function getResponseReturnUrl() {
		$baseUrl = $this->objStoreManagerInterface->getStore()->getBaseUrl();
		return $baseUrl . 'payso/payment/response';
	}
    
    // Get the merchant website return URL.
	function getSuccessReturnUrl() {
	 	$baseUrl = $this->objStoreManagerInterface->getStore()->getBaseUrl();
	 	return $baseUrl . 'payso/payment/success';
	}

	// Get the merchant website return URL.
	// function getFailReturnUrl() {
	// 	$baseUrl = $this->objStoreManagerInterface->getStore()->getBaseUrl();
	// 	return $baseUrl . 'payso/payment/fail';
    // }
    
	// Get the merchant website return URL.
	// function getCancelReturnUrl() {
	// 	$baseUrl = $this->objStoreManagerInterface->getStore()->getBaseUrl();
	// 	return $baseUrl . 'payso/payment/cancel';
    // }

    // Get Payment Getway redirect url to redirect Test URL or Live URL to PG. 
    // It is depending upon the Merchant selected settings in configurations.
	function getPaymentGetwayRedirectUrl() {
		$mode = (int)$this->objConfigSettings['mode'];
		if ($mode) {
			return 'https://www.thaiepay.com/epaylink/payment.aspx';
		} else {
			return 'https://www.thaiepay.com/epaylink/payment.aspx';
		}
	}
    
    // This function is used to genereate the request for make payment to payment getaway.
	public function psConstructRequest($parameter, $isLoggedIn) {
        $this->generatePaysoCommonFormFields($parameter);
        $html = '<form name="psForm" action="' . $this->getPaymentGetwayRedirectUrl() . '" method="post">';
		foreach ($this->arrayPaysoFormFields as $key => $value) {
			if (!empty($value)) {
				$html .= '<input type="hidden" name="' . htmlentities($key) . '" value="' . htmlentities($value) . '" />';
			}
        }
        $html .= '<input type="hidden" name="form_key" value="'. $this->getFormKey() .'" />';
		$html .= '</form>';
		$html .= '<script type="text/javascript">';
		$html .= 'document.psForm.submit()';
		$html .= '</script>';
	$this->_logger->info('DATA_REQUEST', ['value' => $this->arrayPaysoFormFields]);
        return $html;

        // return $this->arrayPaysoFormFields;

	}
    
}
