<?php

namespace PaySolutions\Base\Controller\Jumpapp;

use Magento\Framework\App\Action\Context;

class Postback extends \Magento\Framework\App\Action\Action 
{

    protected $request;
    protected $messageManager;
    protected $_urlInterface;

    public function __construct(
        Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\App\Request\Http $request
    ) {
        parent::__construct($context);
        $this->request = $request;
        $this->_urlInterface = $urlInterface;
        $this->messageManager = $messageManager;
    }

    public function lineNotify($msg){
        $url = "https://notify-api.line.me/api/notify";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $headers = array(
            "Content-Type: application/x-www-form-urlencoded",
            "Authorization: Bearer BjP9UmmAJ6BK2AEUb5auBJENJn5U92gRrM6QSEUO3bd",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $data = "message=error:".$msg;
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($curl);
        curl_close($curl);

        return false;
    }


    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        echo "Test Postback<br><br>";
        $params = $this->request->getParams();
        echo '<pre>';
        print_r($params);
        die();
        $amount = $params['amount'];
        $totalAmount = $amount/100;
        $client_id = $params['client_id'];
        $reference_id = $params['reference_id'];
        $result_code = $params['result_code'];
        $signature = $params['signature'];
        $transaction_sn = $params['transaction_sn'];
        print_r($params);

        $resultRedirect = $this->resultRedirectFactory->create();
        $baseWebsiteUrl = $this->_urlInterface->getUrl(); //https://onlineshopping.j-gourmet.com/huahin/

        if( $result_code == '100'){ //--- Payment Success

            $callbackPage = $baseWebsiteUrl."checkout/onepage/success";
            $resultRedirect->setUrl($callbackPage);

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $orderInterface = $objectManager->create('Magento\Sales\Api\Data\OrderInterface'); 

            $order = $orderInterface->loadByIncrementId($reference_id);
            $orderId = $order->getId();
            $orderdata  = $order->getData();

            //----- Check if order exits
            if ( !(isset($orderdata["status"]))){
                $returnFailData = array();
                $returnFailData['errcode']  = '1'; //$ResultCode;
                $returnFailData['debug_msg'] = 'ShopeePay paid order '.$reference_id.' not found in website.';
                echo json_encode($returnFailData);
                $this->lineNotify('ShopeePay paid order '.$reference_id.' not found in website.Please Check');
                return $resultRedirect;
            }

            $order_status = $orderdata["status"];
            $orderAmount = $orderdata["grand_total"];
            
            if ( $totalAmount != $orderAmount){
                $returnFailData = array();
                $returnFailData['errcode']  = '1'; //$ResultCode;
                $returnFailData['debug_msg'] = 'ShopeePay paid order '.$reference_id.' invalid amount.';
                echo json_encode($returnFailData);
                $this->lineNotify('ShopeePay paid order '.$reference_id.' invalid amount.');
                return $resultRedirect;
            }
        
            //----- Prepare Invoice
            if( $order_status == "pending" ){         

                $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
                $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);

                    $quoteRepository = $objectManager->get('\Magento\Quote\Api\CartRepositoryInterface');    
                    $quote = $quoteRepository->get($orderdata["quote_id"]);
                    $quote->setIsActive(0);
                    $quoteRepository->save($quote);

                $orderRepository = $objectManager->get('\Magento\Sales\Api\OrderRepositoryInterface');
                $invoiceService = $objectManager->get('\Magento\Sales\Model\Service\InvoiceService');
                $transactionFactory = $objectManager->get('\Magento\Framework\DB\TransactionFactory'); 
                $invoiceSender = $objectManager->get('\Magento\Sales\Model\Order\Email\Sender\InvoiceSender');   
        
                // db record id
                $orderId = $reference_id; 
                try {

                    if (!$order->getId()) {
                        
                        $this->lineNotify('ShopeePay paid order '.$reference_id.' no longer exists.Please check ออเดอร์หาย');
                        return $resultRedirect;
                    }
                    if(!$order->canInvoice()) {
                    
                        $this->lineNotify('ShopeePay paid order '.$reference_id.' does not allow an invoice to be created.ออกอินวอยไม่ได้');
                        return $resultRedirect;
                    }
                    $invoice = $invoiceService->prepareInvoice($order);
                    if (!$invoice) {
                        $this->lineNotify('ShopeePay paid order '.$reference_id.' cannot save the invoice right now.Please check ออกอินวอยไม่ได้');
                        return $resultRedirect;
                    }
                    if (!$invoice->getTotalQty()) {
                    
                        $this->lineNotify('ShopeePay paid order '.$reference_id.' cannot create invoice without product.Please check ออกอินวอยไม่ได้');
                        return $resultRedirect;
                    }
                    $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                    $invoice->register();
                    $invoice->getOrder()->setCustomerNoteNotify(false);
                    $invoice->getOrder()->setIsInProcess(true);
                    $order->addStatusHistoryComment('ออก Invoice จาก Shopee Pay', false);
                    $transactionSave = $transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
                    $transactionSave->save();

                    // send invoice emails, If you want to stop mail disable below try/catch code
                    try {
                        $invoiceSender->send($invoice);
                    } catch (\Exception $e) {
                        $order->addStatusHistoryComment('We can\'t send the invoice email right now.', false);
                    }
                } catch (\Exception $e) {
                    $this->lineNotify('ShopeePay paid order '.$reference_id.' cannot create invoice.Please check ออกอินวอยไม่ได้');
                }
                return $resultRedirect;
            } 
            else if ( $order_status == "canceled" ){
                $this->lineNotify('ShopeePay order '.$reference_id.' canceled after paid. Please check. ออเดอร์ถูกยกเลิกหลังชำระเงิน');
                return $resultRedirect;
            }
            else{
                return $resultRedirect;
            }

            
        }else if ($result_code == '203') {
            $this->messageManager->addNotice("Order Created. Waiting ShopeePay confirm payment. ออเดอร์ถูกสร้างแล้ว กำลังรอยืนยันการชำระเงินจากระบบ Shopee Pay หากได้รับแล้วทางร้านจะจัดส่งสินค้า");
            $callbackPage = $baseWebsiteUrl."checkout/onepage/success";
            $resultRedirect->setUrl($callbackPage);
            return $resultRedirect;
        }
        else{
            $this->messageManager->addError("Payment Fail! Please try again.ชำระเงินไม่สำเร็จ กรุณาลองใหม่");
            $callbackPage = $baseWebsiteUrl;
            $resultRedirect->setUrl($callbackPage);
        }


        return false;

    }
}

