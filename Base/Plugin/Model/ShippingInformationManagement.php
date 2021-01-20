<?php

namespace Zigzag\Base\Plugin\Model;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use Exception;
use Zigzag\Base\Helper\Data;

class ShippingInformationManagement
{
    /**
     * @var QuoteRepository
     */
    protected $_quoteRepository;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * ShippingInformationManagement constructor.
     * @param QuoteRepository $quoteRepository
     * @param Data $data
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        Data $data
    )
    {
        $this->_quoteRepository = $quoteRepository;
        $this->_helper = $data;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     * @throws NoSuchEntityException
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    )
    {
        /** @var Quote $quote */
        $quote = $this->_quoteRepository->getActive($cartId);

        $attr = $addressInformation->getShippingAddress()->getExtensionAttributes();
        $deliveryDate  = $attr->getZigzagAvailability();

        if ($deliveryDate) {
            try {
                $data = explode('_', $deliveryDate);
                if ($data) {
                    $quote->setZigzagDeliveryFrom(date('Y-m-d H:i:s', strtotime($data[0] . ' ' . $data[1])));
                    $quote->setZigzagDeliveryTo(date('Y-m-d H:i:s', strtotime($data[0] . ' ' . $data[2])));
                }
            } catch (Exception $e) {
                $this->_helper->log('error',"Error Setting ZigZag Delivery Date to Quote\nQuote Id: {$quote->getId()}", $e);
            }
        }
    }
}