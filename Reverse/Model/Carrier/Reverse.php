<?php

namespace Zigzag\Reverse\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use Zigzag\Base\Helper\Shipping;

/**
 * Custom shipping model
 */
class Reverse extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var int
     */
    const ZIGZAG_SHIPPING_TYPE_CODE = 33;

    /**
     * @var string
     */
    const ZIGZAG_SHIPPING_ORDER_STATUSES_PATH = 'carriers/zigzagreverse/order_statuses';

    /**
     * @var string
     */
    const ZIGZAG_SHIPPING_NAME_PATH = 'carriers/zigzagreverse/name';

    /**
     * @var string
     */
    protected $_code = 'zigzagreverse';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var ResultFactory
     */
    protected $rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $rateMethodFactory;

    /**
     * @var Shipping
     */
    protected $_shipping;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param Shipping $shipping
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        Shipping $shipping,
        array $data = []
    )
    {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->_shipping         = $shipping;
    }

    /**
     * Custom Shipping Rates Collector
     *
     * @param RateRequest $request
     * @return Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var Result $result */
        $result = $this->rateResultFactory->create();

        if ($request->getPackageValueWithDiscount() >= $this->getConfigData('free_shipping_subtotal')) {

            /** @var Method $method */
            $method = $this->rateMethodFactory->create();

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($this->_code);
            $method->setMethodTitle($this->getConfigData('name'));

            $shippingCost = (float)$this->getConfigData('shipping_cost');

            $method->setPrice($shippingCost);
            $method->setCost($shippingCost);

            $result->append($method);
        } elseif ($this->getConfigData('showmethod')) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $errorMsg = $this->getConfigData('specificerrmsg');
            $error->setErrorMessage(
                $errorMsg ? $errorMsg : __(
                    'Sorry, but this shipping method is not applicable due to destination country or minimum cart amount.'
                )
            );
            return $error;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * @param $trackingNumber
     * @return mixed
     */
    public function getTrackingInfo($trackingNumber)
    {
        return $this->_shipping->getTrackingInfo($trackingNumber, $this->_code, $this->getConfigData('title'));
    }
}
