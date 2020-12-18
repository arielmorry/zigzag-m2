<?php

namespace Zigzag\Base\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Zigzag\Base\Helper\Data;
use Zigzag\Base\Model\Carrier\Zigzag;

class ShippingTypes implements OptionSourceInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @param Data $helper
     */

    public function __construct(Data $helper)
    {
        $this->_helper          = $helper;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->_helper->getConfig(Zigzag::ZIGZAG_SHIPPING_TYPES_FULL_PATH);
        return $options ? json_decode($options, true) : [];
    }
}