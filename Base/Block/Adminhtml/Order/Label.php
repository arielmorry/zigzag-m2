<?php

namespace Zigzag\Base\Block\Adminhtml\Order;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\Information;
use Magento\Store\Model\Store;
use Zigzag\Base\Helper\Data;
use Magento\Framework\DataObject;
use Laminas\Barcode\Barcode;
use Magento\Store\Model\StoreRepository;

class Label extends Template
{
    protected $_orders = [];

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var Information
     */
    protected $_storeInfo;

    /**
     * @var Store
     */
    protected $_store;

    /**
     * @var StoreRepository
     */
    protected $_storeRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param Store $store
     * @param Information $storeInfo
     * @param Data $helper
     * @param StoreRepository $storeRepository
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Store $store,
        Information $storeInfo,
        Data $helper,
        StoreRepository $storeRepository,
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);


        $orderId  = $context->getRequest()->getParam('order_id');
        $orderIds = $context->getRequest()->getParam('selected');

        if ($orderId) {
            $this->_orders[] = $orderRepository->get($orderId);
        } elseif ($orderIds) {
            foreach ($orderIds as $orderId) {
                $this->_orders[] = $orderRepository->get($orderId);
            }
        }

        $this->_store           = $store;
        $this->_storeInfo       = $storeInfo;
        $this->_helper          = $helper;
        $this->_storeRepository = $storeRepository;

    }

    /**
     * Get Store info object
     *
     * @param OrderInterface $order
     * @return DataObject
     */
    public function getStoreInfo($order)
    {
        $store = $this->_storeRepository->get($order->getStoreId());
        return $this->_storeInfo->getStoreInformationObject($store);

    }

    /**
     * @param $order
     * @return string
     */
    public function getBarcodeBase64($order)
    {
        $renderer = Barcode::factory(
            'code128',
            'image',
            [
                'barHeight'     => 80,
                'barThickWidth' => 6,
                'barThinWidth'  => 2,
                'text'          => $this->getTrackingNumber($order)
            ],
        );

        ob_start();
        $renderer->render();
        $imageData = ob_get_clean();
        return 'data:image/png;base64,' . base64_encode($imageData);

    }

    /**
     * @return array|OrderInterface[]
     */
    public function getOrders()
    {
        return $this->_orders;
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function getShipmentType($order)
    {
        return $this->_helper->getShipmentCodeByCarrierCode(
            $order->getShippingMethod(true)->getCarrierCode()
        );
    }

    /**
     * @param OrderInterface $order
     * @return string|bool
     */
    public function getTrackingNumber($order)
    {
        return $order->getTracksCollection()->count() ?
            $order->getTracksCollection()->getFirstItem()->getTrackNumber() :
            false;
    }


}