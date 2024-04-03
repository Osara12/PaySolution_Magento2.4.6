<?php

namespace PaySolutions\Base\Controller\Callback;

use Magento\Framework\App\ResponseInterface;

class Return extends \Magento\Framework\App\Action\Action {

    protected $_checkoutSession;
    protected $_orderFactory;
    protected $_scopeConfig;
    protected $_pageFactory;
    protected $orderManagement;

    protected $_invoiceService;
    protected $_orderRepository;
    protected $_transaction;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_pageFactory = $pageFactory;
        $this->orderManagement = $orderManagement;
        $this->_orderRepository = $orderRepository;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->_scopeConfig = $scopeConfig;
        return parent::__construct($context);
        $this->_scopeConfig = $context->getScopeConfig();
        
    }

    // Use this method to get ID    
    public function getRealOrderId()
    {
        $lastorderId = $this->_checkoutSession->getLastOrderId();
        return $lastorderId;
    }

    public function getOrder()
    {
        if ($this->_checkoutSession->getLastRealOrderId()) {
             $order = $this->_orderFactory->create()->loadByIncrementId($this->_checkoutSession->getLastRealOrderId());
        return $order;
        }
        return false;
    }

    public function getShippingInfo()
    {
        $order = $this->getOrder();
        if($order) {
            $address = $order->getShippingAddress();    

            return $address;
        }
        return false;

    }
    
    public function execute()
    {
    
        $invoiceSet = $this->_scopeConfig->getValue('payment/kbankonlinepaymentqr/auto_invoice', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        if( $invoiceSet == '1' ){
        
		$orderId = $this->getRealOrderId().'<br>';
       		$order = $this->_orderRepository->get($orderId);
        	if($order->canInvoice()) {
            		$invoice = $this->_invoiceService->prepareInvoice($order);
            		$invoice->register();
            		$invoice->save();
            		$transactionSave = $this->_transaction->addObject(
                		$invoice
            		)->addObject(
                		$invoice->getOrder()
            		);
            		$transactionSave->save();
            		$this->invoiceSender->send($invoice);
            		//send notification code
            		$order->addStatusHistoryComment(
               			__('Notified customer about invoice #%1.', $invoice->getId())
            		)
            		->setIsCustomerNotified(true)
            		->save();
        	}
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        //$resultRedirect->setPath('payso/payment/success');
        $resultRedirect->setPath('checkout/onepage/success');
        return $resultRedirect;
        
    }
    
}


