<?php

namespace Zigzag\Base\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Zigzag\Base\Service\Ws\ShipmentDelivery;

class ShipmentCreated implements ObserverInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var ShipmentDelivery
     */
    protected $_shipmentDelivery;

    /**
     * ShipmentCreated constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param ShipmentDelivery $shipmentDelivery
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ShipmentDelivery $shipmentDelivery
    )
    {
        $this->_orderRepository  = $orderRepository;
        $this->_shipmentDelivery = $shipmentDelivery;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        /** @var string $trackingNumber */
        $trackingNumber = $observer->getEvent()->getTrackingNumber();

        if ($order->getZigzagDeliveryFrom()) {
            $result = $this->_shipmentDelivery->set($order, $trackingNumber);
            if ($result) {
                $requestedDelivery = date('Y-m-d H:i', strtotime($order->getZigzagDeliveryFrom())) . '-' . date('H:i', strtotime($order->getZigzagDeliveryTo()));
                $order->addCommentToStatusHistory('Delivery Date Sent To ZigZag Successfully ('. $requestedDelivery .')');
                $this->_orderRepository->save($order);
            }
        }
    }
}