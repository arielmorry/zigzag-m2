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
        $allow = [
            Order::STATE_NEW,
            Order::STATE_PENDING_PAYMENT,
            Order::STATE_PROCESSING,
            Info::PAYMENTSTATUS_PENDING,
        ];

        $options = $this->orderStatusCollectionFactory->create()->toOptionArray();
        foreach ($options as $k => $option) {
            if (!in_array($option['value'], $allow)) {
                unset($options[$k]);
            }
        }

        return $options;
    }
}