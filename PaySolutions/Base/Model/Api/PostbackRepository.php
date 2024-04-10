<?php

namespace PaySolutions\Base\Model\Api;

use PaySolutions\Base\Api\PostbackInterface;
use Psr\Log\LoggerInterface;


class PostbackRepository implements PostbackInterface
{

    protected $logger;
    protected $request;
    ]
    public function __construct(
        LoggerInterface $logger,
        \Magento\Framework\Webapi\Rest\Request $request
    )
    {
        $this->logger = $logger;
        $this->request = $request;

    }
    /**
     * @inheritdoc
     */
    public function getPost()
    {
        // It will return all params which will pass from body of postman.
        $bodyParams = $this->request->getBodyParams(); 
        return $bodyParams;
   }
}