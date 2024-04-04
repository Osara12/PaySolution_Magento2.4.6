<?php
declare(strict_types=1);

/**
 * @author Tjitse (PaySolutions)
 * Created on 23-08-18 09:31
 */

namespace PaySolutions\OfflinePayments\Model\Gateways;

use PaySolutions\OfflinePayments\Model\Method;

class Cash extends Method
{
    public $_code = 'cash';
    public $_gateWayCode = 'cash';
}