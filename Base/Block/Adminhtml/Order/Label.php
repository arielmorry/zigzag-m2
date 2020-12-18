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

class Label extends Template
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var OrderInterface
     */
    protected $_order;

    /**
     * @var Information
     */
    protected $_storeInfo;

    /**
     * @var Store
     */
    protected $_store;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param Store $store
     * @param Information $storeInfo
     * @param Data $helper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Store $store,
        Information $storeInfo,
        Data $helper,
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $orderId          = $context->getRequest()->getParam('order_id');
        $this->_order     = $orderRepository->get($orderId);
        $this->_store     = $store;
        $this->_storeInfo = $storeInfo;
        $this->_helper    = $helper;
    }

    /**
     * Get Store info object
     *
     * @return DataObject
     */
    public function getStoreInfo()
    {
        return $this->_storeInfo->getStoreInformationObject($this->_store);

    }

    /**
     * @return string
     */
    public function getBarcodeBase64()
    {
        $renderer = Barcode::factory(
            'code128',
            'image',
            [
                'barHeight'     => 80,
                'barThickWidth' => 6,
                'barThinWidth'  => 2,
                'text'          => $this->getTrackingNumber()
            ],
        );

        ob_start();
        $renderer->render();
        $imageData = ob_get_clean();
        return 'data:image/png;base64,' . base64_encode($imageData);

    }

    /**
     * @return bool|OrderInterface
     */
    public function getOrder()
    {
        return $this->_order ?? false;
    }

    public function getShipmentType()
    {
        return $this->_helper->getShipmentCodeByCarrierCode(
            $this->_order->getShippingMethod(true)->getCarrierCode()
        );
    }

    /**
     * @return string|bool
     */
    public function getTrackingNumber()
    {
        return $this->_order->getTracksCollection()->count() ?
            $this->_order->getTracksCollection()->getFirstItem()->getTrackNumber() :
            false;
    }


}