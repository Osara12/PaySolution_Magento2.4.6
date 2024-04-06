<?php

namespace Paysolutions\CraditCard\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\OfflinePayments\Model\Purchaseorder;
use Magento\Framework\App\Request\DataPersistorInterface;

class OrderPaymentSaveBefore implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Construct
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serialize
     * @param \Magento\Webapi\Controller\Rest\InputParamsResolver $inputParamsResolver
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        \Magento\Webapi\Controller\Rest\InputParamsResolver $inputParamsResolver,
        \Magento\Framework\App\State $state
    ) {
        $this->order = $order;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
        $this->_serialize = $serialize;
        $this->inputParamsResolver = $inputParamsResolver;
        $this->_state = $state;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $inputParams = $this->inputParamsResolver->resolve();
        if ($this->_state->getAreaCode() != \Magento\Framework\App\Area::AREA_ADMINHTML) {
            foreach ($inputParams as $inputParam) {
                if ($inputParam instanceof \Magento\Quote\Model\Quote\Payment) {
                    $paymentData = $inputParam->getData('additional_data');
                    
                    $paymentOrder = $order->getPayment();
                    $order = $paymentOrder->getOrder();
                    $quote = $this->quoteRepository->get($order->getQuoteId());
                    $paymentQuote = $quote->getPayment();
                    $method = $paymentQuote->getMethodInstance()->getCode();
                    if ($method == 'payso_card') {
                        if (isset($paymentData['cardtype'])) {
                            $paymentQuote->setData('cardtype', $paymentData['cardtype']);
                            $paymentOrder->setData('cardtype', $paymentData['cardtype']);
                        }
                        
                    }
                }
            }
        }
    }
}