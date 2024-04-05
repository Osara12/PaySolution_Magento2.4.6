<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PaySolutions\Bill\Model\Payment;



/**
 * Pay In Store payment method model
 */
class PaysoPayment extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'payso_bill';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;


  

}
