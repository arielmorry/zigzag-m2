<?php

namespace Zigzag\Base\Plugin\Config\Model;

use Magento\Config\Model\Config as MagentoConfig;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use SimpleXMLElement;
use Zigzag\Base\Helper\Data;
use Zigzag\Base\Model\Carrier\Zigzag;
use Zigzag\Base\Service\Ws\ShippingMethods;

class Config
{
    /**
     * @var ShippingMethods
     */
    protected $_shippingMethods;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var TypeListInterface
     */
    protected $_cacheFrontendPool;

    /**
     * ShippingTypes constructor.
     * @param ShippingMethods $shippingMethods
     * @param Data $helper
     * @param TypeListInterface $cacheTypeList
     * @param Pool $cacheFrontendPool
     */
    public function __construct(
        ShippingMethods $shippingMethods,
        Data $helper,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool
    )
    {
        $this->_helper            = $helper;
        $this->_shippingMethods   = $shippingMethods;
        $this->_cacheTypeList     = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * @param MagentoConfig $config
     * @param $result
     * @return mixed
     */
    public function afterSave(MagentoConfig $config, $result)
    {
        if ($config->getSection() == 'carriers') {
            $this->setShippingTypes();
        }
        return $result;
    }

    /**
     * @return void
     */
    public function setShippingTypes()
    {
        $options             = [];
        $username            = $this->_helper->getConfig(Zigzag::ZIGZAG_SHIPPING_USERNAME_PATH);
        $password            = $this->_helper->getConfig(Zigzag::ZIGZAG_SHIPPING_PASSWORD_PATH);
        $currentShippingType = $this->_helper->getConfig(Zigzag::ZIGZAG_SHIPPING_TYPES_PATH);

        if ($username && $password) {
            /**
             * @var array|SimpleXMLElement[] $options array($shippingTypeCode => $shippingTypeName, ...)
             */
            $result = $this->_shippingMethods->get();
            if ($result) {
                $values = [];
                foreach ($result as $optionId => $option) {
                    $options[] = ['value' => $optionId, 'label' => $option];
                    $values[]  = $optionId;
                }
                $shippingTypeCodes = implode(',', $values);
                if ($options && ($currentShippingType !== $shippingTypeCodes)) {
                    $this->_helper->setConfig(Zigzag::ZIGZAG_SHIPPING_TYPES_PATH, $shippingTypeCodes);
                    $this->_helper->setConfig(Zigzag::ZIGZAG_SHIPPING_TYPES_FULL_PATH, json_encode($options));
                }
            }
        }

        if (!$options) {
            $this->_helper->setConfig(Zigzag::ZIGZAG_SHIPPING_TYPES_PATH, null);
            $this->_helper->setConfig(Zigzag::ZIGZAG_SHIPPING_TYPES_FULL_PATH, null);
        }

        $this->clearCacheAndReload();
    }

    /**
     * @return void
     */
    protected function clearCacheAndReload()
    {
        $this->_cacheTypeList->cleanType('config');
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
        header('Refresh:0');
    }
}