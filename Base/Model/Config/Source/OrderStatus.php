<?php

namespace Zigzag\Base\Model\Config\Source;

use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Magento\Sales\Model\Order;
use Magento\Paypal\Model\Info;

class OrderStatus implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var CollectionFactory $statusCollectionFactory
     */
    protected $orderStatusCollectionFactory;

    /**
     * OrderStatus constructor.
     * @param CollectionFactory $orderStatusCollectionFactory
     */
    public function __construct(CollectionFactory $orderStatusCollectionFactory)
    {
        $this->orderStatusCollectionFactory = $orderStatusCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $ignore = [
            Order::STATE_CANCELED,
            Order::STATE_CLOSED,
            Order::STATE_COMPLETE,
            Order::STATUS_FRAUD,
            Order::STATE_HOLDED,
            Info::ORDER_STATUS_CANCELED_REVERSAL,
            Info::ORDER_STATUS_REVERSED,
        ];

        $options = $this->orderStatusCollectionFactory->create()->toOptionArray();
        foreach ($options as $k => $option) {
            if (in_array($option['value'], $ignore)) {
                unset($options[$k]);
            }
        }

        return $options;
    }
}