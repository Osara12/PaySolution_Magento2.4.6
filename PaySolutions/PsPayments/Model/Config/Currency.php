<?php

/*
 * PaymentModeType is give the model for dropdown page in admin configuration setting page.
 */

namespace PaySolutions\PsPayments\Model\Config;

class Currency implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{		
	    return [
            ['value' => '00', 'label' => __('THB')],
			['value' => '01', 'label' => __('USD')],
			['value' => '02', 'label' => __('JPY')],
			['value' => '03', 'label' => __('SGD')],
			['value' => '04', 'label' => __('HKD')],
			['value' => '05', 'label' => __('EUR')],
			['value' => '06', 'label' => __('GBP')],
			['value' => '07', 'label' => __('AUD')],
			['value' => '08', 'label' => __('CHF')]
        ];
	}
}