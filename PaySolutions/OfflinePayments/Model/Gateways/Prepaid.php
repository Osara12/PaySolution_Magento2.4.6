<?php
declare(strict_types=1);

/**
 * @author Tjitse (PaySolutions)
 * Created on 23-08-18 09:32
 */

namespace PaySolutions\OfflinePayments\Model\Gateways;

use PaySolutions\OfflinePayments\Model\Method;

class Prepaid extends Method
{
    public $_code = 'prepaid';
    public $_gatewayCode = 'prepaid';
}