<?php

namespace PaySolutions\Base\Block\Adminhtml\Order\View;

class Inquiry extends \Magento\Framework\View\Element\Template
{
	public function __construct(\Magento\Framework\View\Element\Template\Context $context)
	{
		parent::__construct($context);
	}

	public function sayHello()
	{
		return __('Hello World');
	}
}