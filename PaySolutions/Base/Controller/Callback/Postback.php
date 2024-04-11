<?php

namespace PaySolutions\Base\Controller\Callback;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;


class Postback extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface 
{

    protected $_checkoutSession;
    protected $_orderFactory;
    protected $_scopeConfig;
    protected $_pageFactory;
    protected $orderManagement;
    private $urlInterface;

    protected $_invoiceService;
    protected $_orderRepository; 
    protected $_transaction;
    protected $request;
    protected $_orderInterface;
    protected $_cartRepositoryInterface;
    protected $_transactionFactory;
    

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->urlInterface = $urlInterface;
        $this->_pageFactory = $pageFactory;
        $this->_orderInterface = $orderInterface;
        $this->_cartRepositoryInterface = $cartRepositoryInterface;
        $this->request = $request;
        $this->orderManagement = $orderManagement;
        $this->_orderRepository = $orderRepository;
        $this->_invoiceService = $invoiceService;
        $this->_transactionFactory = $transactionFactory;
        $this->_transaction = $transaction;
        $this->_scopeConfig = $scopeConfig;
        return parent::__construct($context);
        $this->_scopeConfig = $context->getScopeConfig();
        
    }
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
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
    public function lineNotify($msg){
        $url = "https://notify-api.line.me/api/notify";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $headers = array(
            "Content-Type: application/x-www-form-urlencoded",
            "Authorization: Bearer 52aYqBDOHN7HmzdiEb6fED0D1adi4420QFr8iIXIT27",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $data = "message=".$msg;
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($curl);
        curl_close($curl);

        return false;
    }
    public function execute()
    {

        $post = $this->getRequest()->getPostValue();

        $refno = $post['refno'];
        //$orderno = $post['orderno'];
        $total = $post['total'];
        $status = $post['status'];
        
        if($refno == null){
            echo "fail to get param 'refno'";
            die();
        }
        if($total == null){
            echo "fail to get param 'total'";
            die();
        }
        if($status == null){
            echo "fail to get param 'status'";
            die();
        }




        $order = $this->_orderInterface->loadByIncrementId($refno);
        $orderdata  = $order->getData();

        //----- Check payment status
        if ($status != "CP"){
            $order->addStatusHistoryComment("Payment Fail status: ".$status, false);
            echo "Payment Fail status: ".$status;
            exit();
        }
        //----- Check if order exits
        if ( !(isset($orderdata["status"]))){
            echo "Order ".$refno." not found in Magento.";
            exit();
        }
        $order_status = $orderdata["status"];
        $orderAmount = $orderdata["grand_total"];
        //----- Check payment amount
        if ( $total != $orderAmount){
            $order->addStatusHistoryComment("Error!!! Payment amount missmatch. PaySolutions: ".$total."Thb | Magento: ".$orderAmount."Thb", false);
            echo "Payment amount missmatch. PaySolutions: ".$total."Thb | Magento: ".$orderAmount."Thb";
            exit();
        }

        //----- Prepare Invoice
        if( $order_status == "pending" ){         
            
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
 
                $quote = $this->_cartRepositoryInterface->get($orderdata["quote_id"]);
                $quote->setIsActive(0);
                $this->_cartRepositoryInterface->save($quote);

     
            // db record id
            try {
                //----- Check order invoice
                if(!$order->canInvoice()) {
                    $order->addStatusHistoryComment('Error!!! This order '.$refno.' does not allow an invoice to be created.');
                    echo 'Error!!! This order '.$refno.' does not allow an invoice to be created.';
                    exit();
                }
                //----- Start Create invoice
                $invoice = $this->_invoiceService->prepareInvoice($order);
                if (!$invoice) {
                    $order->addStatusHistoryComment('Error!!! Order '.$refno.' cannot save the invoice right now.');
                    echo 'Error!!! Order '.$refno.' cannot save the invoice right now.';
                    exit();
                }

                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $invoice->getOrder()->setIsInProcess(true);
                $order->addStatusHistoryComment('ออก Invoice อัตโนมัติจากการชำระเงินด้วย PaySolutions ยอดเงิน '.$total." Thb", false);
                $transactionSave = $this->_transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
                $transactionSave->save();

                // send invoice emails, If you want to stop mail disable below try/catch code
                try {
                    $invoiceSender->send($invoice);
                } catch (\Exception $e) {
                    //$this->messageManager->addError(__('We can\'t send the invoice email right now.'));
                    $order->addStatusHistoryComment('Failed to send invoice email to customer.', false);
                }
                echo "success";
                die();
            } catch (\Exception $e) {
                echo $e;
                exit();
            }

        } 
        /* else if ( $order_status == "canceled" ){
            $returnFailData = array();
            $returnFailData['errcode']  = '1'; //$ResultCode;
            $returnFailData['debug_msg'] = 'Order canceled';
            echo json_encode($returnFailData);
            $this->lineNotify('ShopeePay order '.$payment_reference_id.' canceled before paid. Please check.');
            exit();
        }*/
        else {
            echo "Order ".$refno." status is not pending.";
            exit();
        }
    } 

}