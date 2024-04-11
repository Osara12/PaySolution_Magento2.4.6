<?php

namespace PaySolutions\Base\Model\Api;

use PaySolutions\Base\Api\PostbackInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class PostbackRepository implements PostbackInterface
{

    protected $logger;
    protected $request; 

    protected $_checkoutSession;
    protected $_orderFactory;
    protected $_pageFactory;
    protected $orderManagement;
    private $urlInterface;

    protected $_invoiceService;
    protected $_orderRepository; 
    protected $_transaction;
    protected $_orderInterface;
    protected $_cartRepositoryInterface;
    protected $_transactionFactory;

    
    public function __construct(
        LoggerInterface $logger,
        \Magento\Framework\Webapi\Rest\Request $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    )
    {
        $this->logger = $logger;
        $this->request = $request;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->urlInterface = $urlInterface;
        $this->_pageFactory = $pageFactory;
        $this->_orderInterface = $orderInterface;
        $this->_cartRepositoryInterface = $cartRepositoryInterface;
        $this->orderManagement = $orderManagement;
        $this->_orderRepository = $orderRepository;
        $this->_invoiceService = $invoiceService;
        $this->_transactionFactory = $transactionFactory;
        $this->_transaction = $transaction;
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

    /**
     * @inheritdoc
     */
    public function getPost()
    {
        // It will return all params which will pass from body of postman.
        $bodyParams = $this->request->getBodyParams(); 

        /*---STATUS MEANING
            CP	Completed  รายการสั่งซื้อ "อนุมัติ"
            Y	Completed  รายการสั่งซื้อ "อนุมัติ"
            NS	Not Submit  ลูกค้าของคุณยังไม่ได้กรอกข้อมูลบัตรเครดิต
            N	Not Submit  ลูกค้าของคุณยังไม่ได้ชำระเงิน
            RE	Rejected  รายการสั่งซื้อ "ไม่อนุมัติ"
            RF	Refund  รายการที่ "คืนเงิน" เรียบร้อย
            RR	Request Refund  รายการที่ทำเรื่องขอ "คืนเงิน"
            TC	Test Complete  รายการทดสอบที่ "อนุมัติ" (ใช้บัตรทดสอบทำรายการ ไม่ใช่การชำระเงินจริง)
            VC	VBV Checking  รายการที่อยู่ในระหว่าง "ตรวจสอบ VBV"
            VO	Voided  รายการที่ "คืนเงิน" เรียบร้อย
            VR	VBV Rejected  รายการสั่งซื้อ "ไม่อนุมัติ" เนื่องจากกรอกรหัส VBV ไม่ผ่าน
            N	UnPaid  ลูกค้าไม่ชำระเงิน
            C	Cancel  รายการสั่งซื้อถูกยกเลิก
            HO	Hold  รายการสั่งซื้อถูก "ยึดหน่วง" ยอดการชำระเงินเอาไว้ เนื่องจากรายการสั่งซื้อดังกล่าวอาจมีปัญหาจากการถูกปฏิเสธการชำระเงินได้ในภายหลัง(โปรดอ่านคำแนะนำในอีเมลที่แจ้งเปลี่ยนสถานะเป็น HOLD)
            PF	Payment Failed  รายการสั่งซื้อไม่สำเร็จ  (เกิดเฉพาะรายการ Internet Banking)
        */

        $refno = $bodyParams['refno'];
        $orderno = $bodyParams['orderno'];
        $total = $bodyParams['total'];
        $status = $bodyParams['status'];
        $statusname = $bodyParams['statusname'];

        if($refno == null){
            return "fail to get param 'refno'";
            die();
        }


        $order = $this->_orderInterface->loadByIncrementId($refno);
        $orderdata  = $order->getData();

        //----- Check payment status
        if ($status != "CP"){
            $order->addStatusHistoryComment("Payment Fail status: ".$status.": ".$statusname, false);
            return "Payment Fail status: ".$status.": ".$statusname;
            exit();
        }
        //----- Check if order exits
        if ( !(isset($orderdata["status"]))){
            return "Order ".$refno." not found in Magento.";
            exit();
        }
        $order_status = $orderdata["status"];
        $orderAmount = $orderdata["grand_total"];
        //----- Check payment amount
        if ( $total != $orderAmount){
            $order->addStatusHistoryComment("Error!!! Payment amount missmatch. PaySolutions: ".$total."Thb | Magento: ".$orderAmount."Thb", false);
            return "Payment amount missmatch. PaySolutions: ".$total."Thb | Magento: ".$orderAmount."Thb";
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
                    return 'Error!!! Order '.$refno.' cannot save the invoice right now.';
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
                return "success";
                die();
            } catch (\Exception $e) {
                return $e;
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
            return "fail";
            exit();
        }
    } 

}