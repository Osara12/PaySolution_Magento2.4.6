<?php

namespace PaySolutions\Base\Model;

use PaySolutions\Base\Api\TestInterface;
use PaySolutions\Base\ResourceModel\Post\CollectionFactory;

class Test implements TestInterface
    {
        private $CollectionFactory;
        public function __construct(
            CollectionFactory $CollectionFactory
        )
        {
            $this->CollectionFactory = $CollectionFactory;
        }

    /**
    * {@inheritdoc}
    */
    public function setData($data)
    {
        $name = $data['name'];
        $number = $data['number'];
        $city = $data['city'];


        return "successfully saved";
    }
}