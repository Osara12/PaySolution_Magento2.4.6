<?php

namespace PaySolutions\PsPayments\Controller\Payment;

use Magento\Framework\App\Action\Action;

class Response extends Action {

	const PATH_CART = 'checkout/cart';
	const PATH_SUCCESS = 'checkout/onepage/success';

	protected $_logger;
	protected $_orderFactory;
	protected $_objCheckoutHelper;
	protected $_configSettings;
    protected $_orderRepository;
    protected $_invoiceService;
    protected $_transaction;
	protected $_invoiceSender;
	protected $_session;
	protected $_orderSender;
	protected $_orderCommentSender;
	protected $_transactionBuilder;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Psr\Log\LoggerInterface $logger,
		\Magento\Sales\Model\OrderFactory $orderFactory,
		\PaySolutions\PsPayments\Helper\Checkout $checkoutHelper,
		\Magento\Framework\App\Config\ScopeConfigInterface $configSettings,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
		\Magento\Framework\DB\Transaction $transaction,
		\Magento\Checkout\Model\Session $session,
		\Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
		\Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender,
		\Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
	) {
        parent::__construct($context);
		$this->_logger 				= $logger;
		$this->_orderFactory 		= $orderFactory;
		$this->_objCheckoutHelper 	= $checkoutHelper;
		$this->_configSettings		= $configSettings->getValue('payment/payso_payment');
		$this->_orderRepository 	= $orderRepository;
        $this->_invoiceService 		= $invoiceService;
		$this->_transaction 		= $transaction;
		$this->_session				= $session;
		$this->_orderSender			= $orderSender;
		$this->_orderCommentSender	= $orderCommentSender;
		$this->_invoiceSender 		= $this->_objectManager->get('Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
		$this->_transactionBuilder 	= $transactionBuilder;
	}


	public function execute() { 
		$this->_logger->info('DATA_RESPONSE', ['value' => $_REQUEST]);
		// $this->_logger->info('DATA_RESPONSE', ['value' => $this->getRequest()->getPostValue()]);

		//$order 		= $this->_session->getLastRealOrder();
		//$order_id 	= $order->getIncrementId();

		// if have data response => success
		if (!empty($_REQUEST)) {
			try {

				$order_id 	= substr($_REQUEST['refno'], 1);
				$order 		= $this->getOrderDetailByOrderId($order_id);

				$payment = $order->getPayment();
				$payment->setTransactionId($order_id);
				$payment->setLastTransId($order_id);

				// Update order state and status.
				$order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
				$order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);

				$invoice = $this->invoice($order);
				$invoice->setTransactionId($order_id);

				$payment->addTransactionCommentsToOrder(
					$payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE),
					__(
						'Amount of %1 has been paid via Payso payment',
						$order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal())
					)
				);

				$payment_id = $payment->getId();
				$orderId	= $order->getId();

				// $this->_logger->info('DATA_ID', ['paymentId' => $payment_id, 'orderId' => $orderId]);

				$detailData = [
                    \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => [
						'Order Id' 			=> 	$order_id,
						'Merchant Id'		=>	isset($_REQUEST['merchantid']) ? $_REQUEST['merchantid']  : '',
						'Customer Email'	=>	isset($_REQUEST['customeremail']) ? $_REQUEST['customeremail'] : '',
						'Product Detail'	=>	isset($_REQUEST['productdetail']) ? $_REQUEST['productdetail'] : '',
						'Total'				=>	isset($_REQUEST['total']) ? $_REQUEST['total'] : '',
						'Card Type'			=>	isset($_REQUEST['cardtype']) ? $_REQUEST['cardtype'] : '',
                    ]
				];
				
				$transaction = $this->_transactionBuilder
                    				->setPayment($payment)
                    				->setOrder($order)
                    				->setTransactionId($order_id)
                   					->setAdditionalInformation($detailData)
                    				->setFailSafe(true)
									->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);
									
				
				// create invoice
				if($this->_configSettings['auto_invoice'] == 1) {
					if ($order->canInvoice()) {
						$invoice = $this->_invoiceService->prepareInvoice($order);
						$invoice->register();
						$invoice->save();
						$transactionSave = $this->_transaction->addObject(
							$invoice
						)->addObject(
							$invoice->getOrder()
						);
						$transactionSave->save();
						$this->_invoiceSender->send($invoice);
						// send notification code
						$order->addStatusHistoryComment(
							__('Notified customer about invoice #%1.', $invoice->getId())
						)->setIsCustomerNotified(true)->save();
					}
				} 
				else {
					$order->save();
					$transaction->save();
					$payment->save();
				}
						
				// send mail successed
				$this->_orderSender->send($order);			

			}
			catch(Exception $e) {
				$this->_logger->info('EXCEPTION', ['value' => $e->getMessage()]);
			}

			return;

		} 
		// else {
		// 	// if no have data response => cancel
		// 	// send mail canceled
			
		// 	// 	echo "CANCELED";		
		// 	// 	$this->messageManager->addError(__("Payment error"));
		// 	// 	$this->executeCancelAction();
		// 	// 	$this->_eventManager->dispatch('order_cancel_after', ['order' => $order]);
		// 	// 	$strComment = "Order has been cancelled";

		// 	// 	$order->addStatusHistoryComment($strComment)->setEntityName('order')->save();
		// 	// 	$this->_orderCommentSender->send($order, true, $strComment);

		// 	return;

		// }

	}


	/**
	 * @param  \Magento\Sales\Model\Order $order
	 *
	 * @return \Magento\Sales\Api\Data\InvoiceInterface
	 */
	protected function invoice(\Magento\Sales\Model\Order $order) {
		return $order->getInvoiceCollection()->getLastItem();
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

    // Get the checkout object. It is reponsible for hold the current users cart detail's
    protected function getCheckoutHelper() {
        return $this->_objCheckoutHelper;
    }

    // This function is redirect to cart after customer is cancel the payment.
    protected function executeCancelAction() {
        $this->getCheckoutHelper()->cancelCurrentOrder('');
        $this->getCheckoutHelper()->restoreQuote();
    }

	
}
