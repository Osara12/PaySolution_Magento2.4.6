<?php

namespace PaySolutions\Base\Block\Adminhtml\System\;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field as FormField;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;;

class ReturnUrl extends FormField
{

    private $objStoreManagerInterface;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManagerInterface,
        array $data = []
    ) {
        $this->objStoreManagerInterface = $storeManagerInterface;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     * @throws LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $elementId = explode('_', $element->getHtmlId());
        $baseUrl = $this->objStoreManagerInterface->getStore()->getBaseUrl();
        $webUrl = $baseUrl . 'paysopayment/callback/returnurl ';
        
        $html = '<input style="opacity:1;" readonly id="' . $element->getHtmlId() . '" class="input-text admin__control-text" value="' . $webUrl . '" onclick="this.select()" type="text">';

        return $html;
    }
}
