<?php

namespace Zigzag\Base\Plugin\Block\Adminhtml\Order;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class View
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;


    /**
     * View constructor.
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->_orderRepository = $orderRepository;
    }

    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\View $view
     */
    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $view)
    {
        $this->addShippingLabelButton($view);
        $this->addZigZag($view);
    }

    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\View $view
     */
    protected function addZigZag(\Magento\Sales\Block\Adminhtml\Order\View $view)
    {
        $url = $view->getUrl('zigzag/order/ship', ['order_id' => $view->getOrderId()]);
        $view->addButton(
            'order_ship_to_zigzag',
            [
                'label'   => __('Ship Order To ZigZag'),
                'onclick' => 'setLocation(\'' . $url . '\')',
                'class'   => 'ship-to-zigzag'
            ]
        );
    }

    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\View $view
     */
    protected function addShippingLabelButton(\Magento\Sales\Block\Adminhtml\Order\View $view)
    {
        /** @var Order $order */
        $order = $this->_orderRepository->get($view->getOrderId());
        $carrierCode = $order->getShippingMethod(true)->getCarrierCode();
        if (strpos($carrierCode, 'zigzag') !== false) {
            $url = $view->getUrl('zigzag/order/label', ['order_id' => $view->getOrderId()]);
            $view->addButton(
                'order_print_zigzag_label',
                [
                    'label'   => __('Print ZigZag Shipment Label'),
                    'onclick' => "window.open('$url','popUpWindow','height=700,width=700,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no,status=yes');",
                    'class'   => 'print-label',
                ]
            );
        }
    }
}