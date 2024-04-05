<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paysolutions\Base\Controller\Jumpapp;

class Request extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;

    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $_urlInterface;

    protected $_checkoutSession;

    protected $messageManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_urlInterface = $urlInterface;
        $this->_checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $baseWebsiteUrl = $this->_urlInterface->getUrl(); //https://onlineshopping.j-gourmet.com/huahin/
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        // Mode
        echo "Payso Test Mode";

        //$shopeeUrl = 'https://api.uat.wallet.airpay.co.th/v3/merchant-host/order/create'; //sandbox url

        $merchantId = $this->scopeConfig->getValue('payment/payso_payment/merchant_id', $storeScope);

        $returnUrl = $baseWebsiteUrl."paysopayment/callbacl/postback/";;
        $postbackUrl = $baseWebsiteUrl."paysopayment/callbacl/returnurl/";;

        //Order Details
        $order = $this->_checkoutSession->getLastRealOrder();
        $orderId = $order->getIncrementId();
        $customerEmail = $order->getCustomerEmail();
        if( is_null($orderId) == 1){
            $this->messageManager->addError(__("Error: Order Id not found. ไม่สามารถโหลดข้อมูลออเดอร์"));
            $redirect->setUrl($baseWebsiteUrl);
            return $redirect;
        }
        $refno = $orderId;
        $amount = (int)$order->getGrandTotal()*100; //(int) 1234.56 Bath
        $product = $order->getAllItems();

        //Signature Hash(sha256)
        $bodyEx = '{
                    "merchantid":"'.$merchantId.'",
                    "refno":"'.$refno.'",
                    "customeremail":"'.$customerEmail.'",
                    "productdetail":"'.$product.'",
                    "total":'.$amount.',
                    "lang":"TH",
                    "cc":"00",
                    "channel":"",
                    "returnurl":"'.$returnUrl.'",
                    "postbackurl":"'.$postbackUrl.'"
                }';
        print_r($bodyEx);
        die();

        $hash = hash_hmac('sha256', $bodyEx, $secretKey, true);
        $signature = base64_encode($hash);

        //Send Api Request
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $shopeeUrl ,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>$bodyEx,
        CURLOPT_HTTPHEADER => array(
            'X-Airpay-ClientId: '.$clientID,
            'X-Airpay-Req-H: '.$signature,
            'Content-Type: text/plain'
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        
        $data = json_decode($response, true);
        $errorCode = $data['errcode'];
        $debugMsg = $data['debug_msg'];
        $redirectUrl = $data['redirect_url_http'];


        if( $errorCode != 0){
            $this->messageManager->addError(__("Error(".$errorCode.")".$debugMsg."): Cannot connect Shopee Pay service. ไม่สามารถเชื่อมต่อ Shopee Pay กรุณาลองใหม่อีกครั้ง"));
            $redirect->setUrl($baseWebsiteUrl);
            return $redirect;
        }


        $redirect->setUrl($redirectUrl);
        return $redirect;
    }
}

