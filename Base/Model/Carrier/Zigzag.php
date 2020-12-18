<?php

namespace Zigzag\Base\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Psr\Log\LoggerInterface;

/**
 * Custom shipping model
 */
class Zigzag extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    const ZIGZAG_SHIPPING_TYPES_PATH = 'carriers/zigzagbase/shipping_types';

    /**
     * @var string
     */
    const ZIGZAG_SHIPPING_TYPES_FULL_PATH = 'carriers/zigzagbase/shipping_types_full';

    /**
     * @var string
     */
    const ZIGZAG_SHIPPING_USERNAME_PATH = 'carriers/zigzagbase/username';

    /**
     * @var string
     */
    const ZIGZAG_SHIPPING_PASSWORD_PATH = 'carriers/zigzagbase/password';

    /**
     * @var string
     */
    const ZIGZAG_SHIPPING_EMAIL_PATH = 'carriers/zigzagbase/email';

    /**
     * @var string
     */
    protected $_code = 'zigzagbase';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        array $data = []
    )
    {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Custom Shipping Rates Collector
     *
     * @param RateRequest $request
     * @return bool
     */
    public function collectRates(RateRequest $request)
    {
        return false;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
