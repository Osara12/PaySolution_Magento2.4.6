<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paysolutions\Base\Controller\Jumpapp;

class Request extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $_product;

    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $_urlInterface;

    protected $_checkoutSession;

    protected $messageManager;

    protected $request;

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
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        array $data = []
    ) {
        $this->resultPageFactory = $resultPageFactory;
        
        $this->scopeConfig = $scopeConfig;
        $this->_urlInterface = $urlInterface;
        $this->_product = $product;
        $this->request = $request;
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
        echo "Pay Solution Request<br>";
        $paysoUrl = 'https://payment.paysolutions.asia/epaylink/payment.aspx';
        $merchantId = $this->scopeConfig->getValue('payment/payso_payment/merchant_id', $storeScope);
        $returnUrl = $baseWebsiteUrl."paysopayment/callback/postback/";
        $postbackUrl = $baseWebsiteUrl."paysopayment/callback/returnurl/";

        echo $paysoUrl.'<br><br>';

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
        $grandTotal = $order->getGrandTotal(); //1234.5600
        $amount = number_format((float)$grandTotal, 2, '.', ''); //1234.56
        $product = $order->getAllItems();
        $orderProduct = ''; $productFlag = 0;
        foreach ($product as $item) {
            $itemNo = $item->getId();
            $productId = $item->getProductId();
            $productName = $this->getProduct($productId)->getName();
            if($productFlag > 0){
                $orderProduct = $orderProduct.', ';
            }
            $orderProduct = $orderProduct.$productName;
            $productFlag = 1;
            
        }
        echo "<br>";

        //----Get Payment and change to Payso payment channel code----
        $paymentMethod = $order->getPayment()->getMethod(); 
        $channel = '';
        if($paymentMethod == "payso_card"){ 
            $cardGetParam = $this->request->getParam('cardtype'); // Get selected card type from url
            if($cardGetParam == null){
                $channel = 'full';
            }else{
                $channel = $cardGetParam;
            }
        }elseif ($paymentMethod == "payso_installment") {
            $channel = "installment";
        }elseif ($paymentMethod == "payso_bill") {
            $channel = "bill";
        }elseif ($paymentMethod == "payso_promptpay") {
            $channel = "promptpay";
        }elseif ($paymentMethod == "payso_alipay") {
            $channel = "alipay";
        }elseif ($paymentMethod == "payso_wechat") {
            $channel = "wechat";
        }elseif ($paymentMethod == "payso_truewallet") {
            $channel = "truewallet";
        }elseif ($paymentMethod == "payso_ibanking") {
            $bankGetParam = $this->request->getParam('ibank'); // Get selected bank type from url
            if($bankGetParam == null){
                $channel = "ibanking";
            }else{
                $channel = $bankGetParam;
            }
        }else{
            $channel = "";
        }
        echo $channel."<br>";

        // Request data
        $bodyEx = '{
                    "merchantid":"'.$merchantId.'",
                    "refno":"'.$refno.'",
                    "customeremail":"'.$customerEmail.'",
                    "productdetail":"'.$orderProduct.'",
                    "total":"'.$amount.'",
                    "lang":"TH",
                    "cc":"00",
                    "channel":"'.$channel.'",
                    "returnurl":"'.$returnUrl.'",
                    "postbackurl":"'.$postbackUrl.'"
                }';
        print_r($bodyEx);
        echo "<br><br><br>";

        //---- Create Form data and redirect
        echo '<form method="post" action="https://payment.paysolutions.asia/epaylink/payment.aspx"><br>';
            echo 'Customer E-mail:';
            echo '<input type="text" name="customeremail" value="'.$customerEmail.'">';
            echo '<br> Product Detail:';
            echo '<input type="text" name="productdetail" value="'.$orderProduct.'">';
            echo '<br>';
            echo 'Reference No.:';
            echo '<input type="text" name="refno" value="'.$refno.'">';
            echo '<br>';
            echo 'Merchant ID:';
            echo '<input type="text" name="merchantid" value="'.$merchantId.'">';
            echo '<br>';
            echo 'Currency Code:';
            echo '<input type="text" name="cc" value="00">';
            echo '<br> Total:';
            echo '<input type="text" name="total" value="'.$amount.'">';
            echo '<br> Lang:';
            echo '<input type="text" name="lang" value="TH">';
            echo '<br> Channel:';
            echo '<input type="text" name="channel" value="'.$channel.'">';
            echo '<br> Return Url:';
            echo '<input type="text" name="returnurl" value="'.$returnUrl.'">';
            echo '<br> Postback Url:';
            echo '<input type="text" name="postbackurl" value="'.$postbackUrl.'">';
            echo '<br>';
            echo '<br>';
            echo '<br>';
            echo '<input type="submit" name="Submit" value="Comfirm Order">';
        echo '</form>';


        return;
    }

    public function getProduct($id)
    {
        return $this->_product->create()->load($id);
    }
}

