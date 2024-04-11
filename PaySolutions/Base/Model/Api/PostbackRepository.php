<?php

namespace PaySolutions\Base\Model\Api;

use PaySolutions\Base\Api\PostbackInterface;
use Psr\Log\LoggerInterface;


class PostbackRepository implements PostbackInterface
{

    protected $logger;
    protected $request;

    
    public function __construct(
        LoggerInterface $logger,
        \Magento\Framework\Webapi\Rest\Request $request
    )
    {
        $this->logger = $logger;
        $this->request = $request;

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

    /**
     * @inheritdoc
     */
    public function getPost()
    {
        // It will return all params which will pass from body of postman.
        $bodyParams = $this->request->getBodyParams(); 
        $this->lineNotify(json_encode($bodyParams));
        return $bodyParams;
   }
}