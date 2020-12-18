<?php

namespace Zigzag\Base\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\MailException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Shipping\Model\ShipmentNotifier;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Exception;
use Zigzag\Base\Service\Ws\ShipmentStatus;

class Shipping extends AbstractHelper
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var ConvertOrder
     */
    protected $_convertOrder;

    /**
     * @var ShipmentNotifier
     */
    protected $_shipmentNotifier;

    /**
     * @var ShipmentRepositoryInterface
     */
    protected $_shipmentRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var TrackFactory
     */
    protected $_trackFactory;

    /**
     * @var StatusFactory
     */
    protected $_trackStatusFactory;

    /**
     * @var ShipmentStatus
     */
    protected $_shipmentStatus;

    /**
     * ShippingTypes constructor.
     * @param Context $context
     * @param ConvertOrder $convertOrder
     * @param ShipmentNotifier $shipmentNotifier
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param TrackFactory $trackFactory
     * @param StatusFactory $statusFactory
     * @param ShipmentStatus $shipmentStatus
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        ConvertOrder $convertOrder,
        ShipmentNotifier $shipmentNotifier,
        ShipmentRepositoryInterface $shipmentRepository,
        OrderRepositoryInterface $orderRepository,
        TrackFactory $trackFactory,
        StatusFactory $statusFactory,
        ShipmentStatus $shipmentStatus,
        Data $helper
    )
    {
        parent::__construct($context);

        $this->_convertOrder       = $convertOrder;
        $this->_shipmentNotifier   = $shipmentNotifier;
        $this->_shipmentRepository = $shipmentRepository;
        $this->_orderRepository    = $orderRepository;
        $this->_trackFactory       = $trackFactory;
        $this->_trackStatusFactory = $statusFactory;
        $this->_shipmentStatus     = $shipmentStatus;
        $this->_helper             = $helper;
    }

    /**
     * @param Order $order
     * @param $carrier
     * @param int $trackingNumber
     */
    public function shipOrder(Order $order, $carrier, $trackingNumber = 0)
    {
        // Save order to repository so we can retrieve full info
        $order = $this->_orderRepository->save($order);

        if (!$order->canShip()) {
            $msg = "Error while creating shipment for ZigZag. Order Can't be shipped\nOrder Number: {$order->getIncrementId()}";
            $this->_helper->log('error', $msg, null, true);
            return;
        }

        $shipment = $this->_convertOrder->toShipment($order);
        try {
            foreach ($order->getAllItems() as $orderItem) {
                if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }

                $qtyShipped   = $orderItem->getQtyToShip();
                $shipmentItem = $this->_convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);

                $shipment->addItem($shipmentItem);
            }

            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);


            // Save Order and Shipment
            $this->_shipmentRepository->save($shipment);
            $this->_orderRepository->save($shipment->getOrder());

            // Add Notification and Tracking
            $this->addTrackingToShipment($trackingNumber, $carrier, $shipment);

            // Save Shipment again
            $this->_shipmentRepository->save($shipment);
        } catch (Exception $e) {
            $this->_helper->log('error', $order->getId());
            $msg = "Error while saving shipment for ZigZag\nError Code: {$e->getCode()}\nError Message: {$e->getMessage()}\nOrder Number: {$order->getIncrementId()}";
            $this->_helper->log('error', $msg, null, true);
        }
    }

    /**
     * @param string $trackingNumber
     * @param $carrier
     * @param Shipment $shipment
     * @return mixed
     * @throws MailException
     */
    public function addTrackingToShipment($trackingNumber, $carrier, $shipment)
    {
        // Add Notification and Tracking
        $track = $this->_trackFactory->create()
            ->setNumber(
                $trackingNumber
            )->setCarrierCode(
                $carrier->getCarrierCode()
            )->setTitle(
                $this->_helper->getConfig($carrier::ZIGZAG_SHIPPING_NAME_PATH)
            );
        $shipment->addTrack($track);
        $this->_shipmentNotifier->notify($shipment);

        return $shipment;
    }

    /**
     * @param Order $order
     * @param string $carrierCode
     * @return bool
     */
    public function isCarrierInOrder($order, $carrierCode)
    {
        $orderCarrierCode = $order->getShippingMethod(true)->getCarrierCode();
        return $carrierCode == $orderCarrierCode;
    }

    /**
     * @param $trackingNumber
     * @param $code
     * @param $title
     * @return mixed
     */
    public function getTrackingInfo($trackingNumber, $code, $title)
    {
        $status   = '';
        $statuses = $this->_shipmentStatus->get($trackingNumber);
        if ($statuses) {
            $latestStatusData = end($statuses);
            $status           = $latestStatusData->TEOR_STATUSCODE ?? '';
        }

        $tracking = $this->_trackStatusFactory->create();
        $tracking->setData([
            'carrier'       => $code,
            'carrier_title' => $title,
            'tracking'      => $trackingNumber,
            'track_summary' => $status
        ]);
        return $tracking;
    }
}