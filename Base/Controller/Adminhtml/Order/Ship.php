<?php

namespace Zigzag\Base\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Zigzag\Base\Helper\Shipping;
use Zigzag\Base\Service\Ws\InsertShipment;
use Magento\Framework\Message\ManagerInterface;

class Ship extends Action
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var InsertShipment
     */
    protected $_insertShipment;

    /**
     * @var Shipping
     */
    protected $_shipping;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var Order[]
     */
    protected $_orders;

    /**
     * Ship constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param InsertShipment $insertShipment
     * @param Shipping $shipping
     * @param ManagerInterface $messageManager
     * @param Context $context
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        InsertShipment $insertShipment,
        Shipping $shipping,
        ManagerInterface $messageManager,
        Context $context
    )
    {
        parent::__construct($context);
        $this->_orderRepository = $orderRepository;
        $this->_insertShipment  = $insertShipment;
        $this->_shipping        = $shipping;
        $this->_messageManager  = $messageManager;
    }


    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $orderId  = $this->getRequest()->getParam('order_id');
        $orderIds = $this->getRequest()->getParam('selected');

        if ($orderId) {
            $this->_orders[] = $this->_orderRepository->get($orderId);
        } elseif ($orderIds) {
            foreach ($orderIds as $orderId) {
                $this->_orders[] = $this->_orderRepository->get($orderId);
            }
        }

        foreach ($this->_orders as $order) {
            if ($order) {
                $result = $this->_insertShipment->insert($order);
                if ($result) {
                    $this->_shipping->shipOrder($order, $result);
                    $this->_messageManager->addSuccessMessage(__('ZigZag Module: Shipment Created Successfully. Tracking Number %1', $result));
                } else {
                    $this->_messageManager->addErrorMessage(__('ZigZag Module: Error occurred. Please Check Error Log'));
                }
            } else {
                $this->_messageManager->addErrorMessage(__('ZigZag Module: Order Not Found'));
            }
        }

        $this->_redirect($this->_redirect->getRefererUrl());
    }
}