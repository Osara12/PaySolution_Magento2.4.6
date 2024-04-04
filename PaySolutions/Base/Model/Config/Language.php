<?php

/*
 * PaymentModeType is give the model for dropdown page in admin configuration setting page.
 */

namespace PaySolutions\PsPayments\Model\Config;

class Language implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
	    return [
            ['value' => 'TH', 'label' => __('ไทย')],
			['value' => 'EN', 'label' => __('English')]
        ];
	}
}
