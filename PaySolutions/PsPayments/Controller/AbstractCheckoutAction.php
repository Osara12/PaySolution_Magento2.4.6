<?php

/*
 * Date 20 June 2017
 * AbstractCheckoutAction is used for intermediate for request and reponse.
 */

namespace PaySolutions\PsPayments\Controller;

use PaySolutions\PsPayments\Controller\AbstractAction;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;

abstract class AbstractCheckoutAction extends AbstractAction
{
    const ROUTE_PATTERN_CHECKOUT_CART_PATH = 'checkout/cart';
    const ROUTE_PATTERN_CHECKOUT_CART_ARGS = [];

    protected $_checkoutSession;
    protected $_orderFactory;

    public function __construct(Context $context, Session $checkoutSession, OrderFactory $orderFactory) {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory    = $orderFactory;
    }

    // Get Magento checkout session.
    protected function getCheckoutSession() {
        return $this->_checkoutSession;
    }

    // Get Magento OrderFactory object.
    protected function getOrderFactory() {
        return $this->_orderFactory;
    }

    // Get Magento Order object.
    protected function getOrderDetailByOrderId($orderId) {
        $order = $this->getOrderFactory()->create()->loadByIncrementId($orderId);
        if (!$order->getId()) {
            return null;
        }
        return $order;
    }

    // Redirect to cart when and restored the previous selected Item.
    protected function redirectToCheckoutCart() {
        $this->_redirect('payso/payment/fail');
    }
}
