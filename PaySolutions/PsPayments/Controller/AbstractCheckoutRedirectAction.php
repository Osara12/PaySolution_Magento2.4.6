<?php

/*
 * AbstractCheckoutRedirectAction is used for intermediate for request and reponse.
 */

namespace PaySolutions\PsPayments\Controller;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\Session as Customer;
use PaySolutions\PsPayments\Controller\AbstractCheckoutAction;
use PaySolutions\PsPayments\Helper\Checkout;
use PaySolutions\PsPayments\Helper\PsRequest;

abstract class AbstractCheckoutRedirectAction extends AbstractCheckoutAction
{
    protected $objCheckoutHelper;
    protected $objCustomer;
    protected $objPsRequestHelper;
    protected $objConfigSettings;
    protected $objCatalogSession;

    public function __construct(
        Context $context,
        Session $checkoutSession, 
        OrderFactory $orderFactory,
        Customer $customer, 
        Checkout $checkoutHelper,
        PsRequest $psRequest,
        ScopeConfigInterface $configSettings ,
        CatalogSession $catalogSession
    ) {
        parent::__construct($context, $checkoutSession, $orderFactory);
        $this->objCheckoutHelper     = $checkoutHelper;
        $this->objCustomer           = $customer;
        $this->objPsRequestHelper    = $psRequest;
        $this->objConfigSettings     = $configSettings->getValue('payment/payso_payment');
        $this->objCatalogSession     = $catalogSession;
    }

    // This object is hold the custom filed data for payment method like selected store Card's, other setting, etc.
    protected function getCatalogSession() {
        return $this->objCatalogSession;
    }

    // Get the Magento configuration setting object that hold global setting for Merchant configuration
    protected function getConfigSettings() {
        return $this->objConfigSettings;
    }

    // Get the request helper class. It is responsible for construct the current user request for Payment Gateway.
    protected function getPsRequest($paramter, $isloggedIn) {
        return $this->objPsRequestHelper->psConstructRequest($paramter, $isloggedIn);
    }

    // This is magento object to get the customer object.
    protected function getCustomerSession() {
        return $this->objCustomer;
    }

    // Get the cehckout object. It is reponsible for hold the current users cart detail's
    protected function getCheckoutHelper() {
        return $this->objCheckoutHelper;
    }

    // This function is redirect to cart after customer is cancel the payment.
    protected function executeCancelAction() {
        $this->getCheckoutHelper()->cancelCurrentOrder('');
        $this->getCheckoutHelper()->restoreQuote();
        $this->redirectToCheckoutCart();
    }
}
