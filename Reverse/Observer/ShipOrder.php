<?php

namespace Zigzag\Reverse\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\MailException;
use Magento\Sales\Model\Order\Shipment;
use Zigzag\Base\Service\Ws\InsertShipment;
use Zigzag\Base\Helper\Shipping;
use Zigzag\Base\Helper\Data;
use Zigzag\Reverse\Model\Carrier\Reverse as Carrier;

class ShipOrder implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var InsertShipment
     */
    protected $_insertShipment;

    /**
     * @var Shipping
     */
    private $_shipping;

    /**
     * @var Carrier
     */
    protected $_carrier;

    /**
     * ShippingTypes constructor.
     * @param Data $helper
     * @param InsertShipment $insertShipment
     * @param Carrier $carrier
     * @param Shipping $shipping
     */
    public function __construct(
        Data $helper,
        InsertShipment $insertShipment,
        Carrier $carrier,
        Shipping $shipping
    )
    {
        $this->_insertShipment = $insertShipment;
        $this->_shipping       = $shipping;
        $this->_carrier        = $carrier;
        $this->_helper         = $helper;
    }

    /**
     * @param Observer $observer
     * @return void|ShipOrder
     * @throws MailException
     */
    public function execute(Observer $observer)
    {
        /** @var Shipment $shipment */
        $shipment = $observer->getEvent()->getShipment();
        if ($shipment->getOrigData('entity_id')) {
            return;
        }

        $order = $shipment->getOrder();
        if ($this->_shipping->isCarrierInOrder($order, $this->_carrier->getCarrierCode())) {
            $result = $this->_insertShipment->insert($order, $this->_carrier, false);
            if ($result) {
                $this->_shipping->addTrackingToShipment($result, $this->_carrier, $shipment);
            }
        }

        return $this;
    }
}