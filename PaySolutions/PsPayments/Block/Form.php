<?php

/*
 * This block class is responsible for give the detail into sucess.phtml file.
 */

namespace PaySolutions\PsPayments\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Model\Currency;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session;

class Form extends \Magento\Framework\View\Element\Template
{
	protected $_objOrder;
	protected $_objCustomerSession;
	protected $_objStoreManagerInterface;
	protected $_checkoutSession;

	public function __construct(
		Context $context, 
		Order $order,
		CustomerSession $customerSession, 
		StoreManagerInterface $storeManagerInterface,
		Session $checkoutSession 
	) {
		parent::__construct($context);
		$this->_objOrder 				= $order;
		$this->_objCustomerSession 		= $customerSession;
		$this->_storeManagerInterface 	= $storeManagerInterface;
		$this->_checkoutSession 		= $checkoutSession;
	}

	public function getResponseParams() {
		return $this->getRequest()->getParams();
	}

	public function getOrderDetails($orderId) {
		return $this->_objOrder->loadByIncrementId($orderId);
	}

	public function getCustomerDetail() {
		return $this->_objCustomerSession;
	}

	public function getBaseCurrencyCode() {
		return $this->_storeManagerInterface->getStore()->getCurrentCurrency()->getCode();
	}

	public function getOrderId() {
		return $this->_checkoutSession->getLastRealOrderId();
	}
}
