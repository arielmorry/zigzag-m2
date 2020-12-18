<?php

namespace Zigzag\Base\Model\Config\Frontend;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;


class ShippingTypes extends Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setDisabled('disabled');
        return $element->getElementHtml();

    }
}