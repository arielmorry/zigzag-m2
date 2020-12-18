<?php

namespace Zigzag\Regular\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Zigzag\Regular\Model\Carrier\Regular as Carrier;
use Zigzag\Base\Helper\Data;

class Yesno implements OptionSourceInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * Yesno constructor.
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $isEnabled = $this->_helper->isShippingTypeEnabledByCarrier(Carrier::ZIGZAG_SHIPPING_TYPE_CODE);
        $options = [
            ['value' => 0, 'label' => __('No')]
        ];

        if ($isEnabled) {
            $options[] =  ['value' => 1, 'label' => __('Yes')];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $isEnabled = $this->_helper->isShippingTypeEnabledByCarrier(Regular::ZIGZAG_SHIPPING_TYPE_CODE);
        $options = [
            0 => __('No')
        ];

        if ($isEnabled) {
            $options[1] =  __('Yes');
        }
        return $options;
    }
}