<?php
namespace Zigzag\Base\Controller\Ajax;

use Magento\Directory\Model\CountryFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Zigzag\Base\Service\Ws\ShipmentAvailability;

class Availability extends Action
{
    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var ShipmentAvailability
     */
    protected $_shipmentAvailability;

    /**
     * @var CountryFactory
     */
    protected $_countryFactory;

    /**
     * Dt constructor.
     * @param Context $context
     * @param JsonFactory $jsonResultFactory
     * @param CountryFactory $countryFactory
     * @param ShipmentAvailability $shipmentAvailability
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonResultFactory,
        CountryFactory $countryFactory,
        ShipmentAvailability $shipmentAvailability
    )
    {
        parent::__construct($context);

        $this->_resultJsonFactory = $jsonResultFactory;
        $this->_countryFactory = $countryFactory;
        $this->_shipmentAvailability = $shipmentAvailability;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $address = $this->_request->getParam('target');
        $countryId = $this->_request->getParam('country_id');
        $country = $this->_countryFactory->create()->loadByCode($countryId)->getName();

        $options = $this->_shipmentAvailability->get($address . ' ' . $country);

        $result = $this->_resultJsonFactory->create();
        $result->setData($options);

        return $result;
    }
}