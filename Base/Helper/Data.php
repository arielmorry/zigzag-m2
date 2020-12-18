<?php

namespace Zigzag\Base\Helper;

use Exception;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Zigzag\Base\Model\Carrier\Zigzag;
use Magento\Shipping\Model\CarrierFactory;

class Data extends AbstractHelper
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var string
     */
    protected $_scope;

    /**
     * @var null|string|int
     */
    protected $_scopeId;

    /**
     * @var null|string|int
     */
    protected $_scopeCode;

    /**
     * @var WriterInterface
     */
    protected $_configWriter;

    /**
     * @var CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * Base constructor.
     * @param Context $context
     * @param WriterInterface $configWriter
     * @param CarrierFactory $carrierFactory
     */
    public function __construct(
        Context $context,
        WriterInterface $configWriter,
        CarrierFactory $carrierFactory
    )
    {
        parent::__construct($context);

        $this->_request        = $context->getRequest();
        $this->_configWriter   = $configWriter;
        $this->_carrierFactory = $carrierFactory;

        // Set Default Scope for config
        $this->_scope     = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $this->_scopeCode = null;
        $this->_scopeId   = 0;

        if ($this->_request->getParam(ScopeInterface::SCOPE_WEBSITE)) {
            $this->_scope     = ScopeInterface::SCOPE_WEBSITES;
            $this->_scopeCode = $this->_scopeId = $this->_request->getParam(ScopeInterface::SCOPE_WEBSITE);
        }

        if ($this->_request->getParam(ScopeInterface::SCOPE_STORE)) {
            $this->_scope     = ScopeInterface::SCOPE_STORES;
            $this->_scopeCode = $this->_scopeId = $this->_request->getParam(ScopeInterface::SCOPE_STORE);
        }
    }

    /**
     * @param string $config_path
     * @param string $scopeType
     * @param null $scopeCode
     * @return mixed
     */
    public function getConfig($config_path, $scopeType = null, $scopeCode = null)
    {

        if (!$scopeType) {
            $scopeType = $this->_scope;
            $scopeCode = $this->_scopeCode;
        }

        return $this->scopeConfig->getValue($config_path, $scopeType, $scopeCode);
    }

    /**
     * @param string $config_path
     * @param string $scopeType
     * @param null $scopeCode
     * @return mixed
     */
    public function isSetFlag($config_path, $scopeType = null, $scopeCode = null)
    {
        if (!$scopeType) {
            $scopeType = $this->_scope;
            $scopeCode = $this->_scopeCode;
        }

        return $this->scopeConfig->isSetFlag($config_path, $scopeType, $scopeCode);
    }

    /**
     * @param string $config_path
     * @param $value
     * @param string $scope
     * @param int $scopeId
     * @return void
     */
    public function setConfig($config_path, $value, $scope = null, $scopeId = 0)
    {
        if (!$scope) {
            $scope   = $this->_scope;
            $scopeId = $this->_scopeId;
        }

        $this->_configWriter->save($config_path, $value, $scope, $scopeId);
    }

    /**
     * @param null|int $shippingCode
     * @return bool
     */
    public function isShippingTypeEnabledByCarrier($shippingCode = null)
    {
        $shippingTypes = $this->getConfig(Zigzag::ZIGZAG_SHIPPING_TYPES_PATH);

        return $shippingTypes ? in_array($shippingCode, explode(',', $shippingTypes)) : false;
    }

    /**
     * @param $level
     * @param string $msg
     * @param null|Exception $e
     * @param bool $sendEmail
     */
    public function log($level, $msg = '', $e = null, $sendEmail = false)
    {
        $msg = is_array($msg) ? print_r($msg, true) : $msg;
        $this->_logger->{$level}($msg, $e ? ['exception' => $e] : []);
        if ($sendEmail) {
            $this->sendErrorNotification($msg, $e);
        }
    }

    /**
     * @param string $msg
     * @param null|Exception $e
     */
    protected function sendErrorNotification(string $msg, $e)
    {
        $from = $this->getConfig('trans_email/ident_general/email');
        $name = $this->getConfig('trans_email/ident_general/name');
        $to   = $this->getConfig(Zigzag::ZIGZAG_SHIPPING_EMAIL_PATH);

        $body = '<div><p>Message: ' . $msg . '</p>';
        if ($e) {
            $body .= '<p>Exception Details:<br>' . $e->getMessage() . '</p>';
        }
        $body .= '</div>';

        if ($to) {
            try {
                $email = new \Zend_Mail();
                $email->setSubject('Error - ZigZag Shipping Module');
                $email->setBodyHtml(nl2br($body));
                $email->setFrom($from, $name);
                $email->addTo($to);
                $email->send();
            } catch (\Zend_Mail_Exception $e) {
                $this->log('error', 'Error sending email from ZigZag Module', $e);
            }
        }
    }

    /**
     * @param string $code
     * @return string
     */
    public function getShipmentCodeByCarrierCode($code = '')
    {
        $result = '';
        if ($code) {
            $carrierModel = $this->_carrierFactory->create($code);
            if ($carrierModel) {
                $shipmentCode  = (int)$carrierModel::ZIGZAG_SHIPPING_TYPE_CODE;
                $shipmentTypes = $this->getConfig(Zigzag::ZIGZAG_SHIPPING_TYPES_FULL_PATH);
                foreach (json_decode($shipmentTypes) as $shipmentType) {
                    if ($shipmentCode === $shipmentType->value) {
                        $result = $shipmentType->label;
                        break;
                    }
                }
            }
        }

        return $result;
    }
}