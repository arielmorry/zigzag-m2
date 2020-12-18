<?php

namespace Zigzag\Regular\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Model\Order;
use Zigzag\Base\Helper\Shipping;
use Zigzag\Base\Service\Ws\InsertShipment;
use Zigzag\Regular\Model\Carrier\Regular as Carrier;

class SalesOrderPlaceAfter implements ObserverInterface
{
    /**
     * @var InsertShipment
     */
    protected $_insertShipment;

    /**
     * @var Carrier
     */
    protected $_carrier;

    /**
     * @var Shipping
     */
    private $_shipping;

    /**
     * ShippingTypes constructor.
     * @param InsertShipment $insertShipment
     * @param Carrier $carrier
     * @param Shipping $shipping
     */
    public function __construct(
        InsertShipment $insertShipment,
        Carrier $carrier,
        Shipping $shipping
    )
    {
        $this->_insertShipment = $insertShipment;
        $this->_shipping       = $shipping;
        $this->_carrier        = $carrier;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     */
    public function execute(EventObserver $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        if ($this->_shipping->isCarrierInOrder($order, $this->_carrier->getCarrierCode())) {
            $result = $this->_insertShipment->insert($order, $this->_carrier);

            if ($result) {
                $this->_shipping->shipOrder($order, $result);
            }
        }

        return $this;
    }
}