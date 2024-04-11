<?php

/*
 * PaymentModeType is give the model for dropdown page in admin configuration setting page.
 */

namespace PaySolutions\PsPayments\Model\Config;

class Mode implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
	    return [
            ['value' => '1', 'label' => __('Test Mode')],
            ['value' => '0', 'label' => __('Live Mode')]
        ];
	}
}
