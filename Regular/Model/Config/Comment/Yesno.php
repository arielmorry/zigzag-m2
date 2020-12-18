<?php

namespace Zigzag\Regular\Model\Config\Comment;

use Magento\Config\Model\Config\CommentInterface;
use Zigzag\Base\Helper\Data;
use Zigzag\Regular\Model\Carrier\Regular as Carrier;

class Yesno implements CommentInterface
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
     * @param string $elementValue
     * @return string|void
     */
    public function getCommentText($elementValue)
    {
        $isEnabled = $this->_helper->isShippingTypeEnabledByCarrier(Carrier::ZIGZAG_SHIPPING_TYPE_CODE);
        if (!$isEnabled) {
            return __('Shipping method <b>Disabled</b> by ZigZag.');
        }
    }
}