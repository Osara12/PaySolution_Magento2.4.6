<?php

namespace PaySolutions\Base\Model\Api;

use PaySolutions\Base\Api\PostbackInterface;
use Psr\Log\LoggerInterface;


class PostbackRepository implements PostbackInterface
{

    protected $logger;
    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }
    /**
     * @inheritdoc
     */
    public function getPost()
    {
        $response = ['success' => false];
        try {
            // Your Code here
            $response = ['success' => true, 'message' => $value];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
            $this->logger->info($e->getMessage());
        }
        $returnArray = json_encode($response);
        return $returnArray; 
   }
}