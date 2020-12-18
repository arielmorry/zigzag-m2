<?php

namespace Zigzag\Base\Model\Config\Comment;

use Magento\Config\Model\Config\CommentInterface;
use Zigzag\Base\Helper\Data;
use Zigzag\Base\Model\Carrier\Zigzag;

class ShippingTypes implements CommentInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * ShippingTypes constructor.
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        $shippingTypes = $this->_helper->getConfig(Zigzag::ZIGZAG_SHIPPING_TYPES_PATH);

        return ($shippingTypes) ? __('This list is controlled by ZigZag (Save configuration to refresh)')
            : __('Please provide username, password and save configuration in order to populate this list. If credentials are set and this list is still empty, please contact ZigZag customer support.');
    }
}