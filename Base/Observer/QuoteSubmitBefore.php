<?php

namespace Zigzag\Base\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\Order;

class QuoteSubmitBefore implements ObserverInterface
{
    /**
     * @var QuoteRepository
     */
    protected $_quoteRepository;

    /**
     * QuoteSubmitBefore constructor.
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        QuoteRepository $quoteRepository
    )
    {
        $this->_quoteRepository = $quoteRepository;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order  = $observer->getEvent()->getOrder();

        /** @var Quote $quote */
        $quote = $this->_quoteRepository->get($order->getQuoteId());

        if ($quote->getZigzagDeliveryFrom()) {
            $order->setZigzagDeliveryFrom($quote->getZigzagDeliveryFrom());
            $order->setZigzagDeliveryTo($quote->getZigzagDeliveryTo());
        }

        return $this;
    }
}