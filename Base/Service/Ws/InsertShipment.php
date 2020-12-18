<?php

namespace Zigzag\Base\Service\Ws;

use GuzzleHttp\Psr7\Response;
use Magento\Sales\Model\Order;
use SimpleXMLElement;
use Exception;
use Zigzag\Base\Service\Base;

class InsertShipment extends Base
{
    /**
     * Base URI for Vendor's Web Service
     */
    const WS_ENDPOINT = 'INSERT_SHLIHUT';

    /**
     * @param Order $order
     * @param $carrier
     * @param bool $checkStatus
     * @return string|void
     */
    public function insert($order, $carrier = null, $checkStatus = true)
    {
        if ($carrier && $checkStatus) {
            $orderStatus     = $order->getStatus();
            $configStatuses  = $this->_helper->getConfig($carrier::ZIGZAG_SHIPPING_ORDER_STATUSES_PATH);
            $allowedStatuses = [];

            if ($configStatuses) {
                $allowedStatuses = explode(',', $configStatuses);
            }

            if (!in_array($orderStatus, $allowedStatuses)) {
                return;
            }
        }

        $shippingType = $carrier ? $carrier::ZIGZAG_SHIPPING_TYPE_CODE : 0;
        $shippingAddress = $order->getShippingAddress();
        $street = implode(' ', $shippingAddress->getStreet());

        $ownerPhone = $this->_helper->getConfig(
            'general/store_information/phone',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $order->getStoreId()
        );

        $data = [
            'KOD_KIVUN'              => 1,
            'MOSER'                  => '',
            'HEVRA_MOSER'            => '',
            'TEL_MOSER'              => $ownerPhone ? preg_replace('/\D/', '', $ownerPhone) : '',
            'EZOR_MOSER'             => 0,
            'SHM_EIR_MOSER'          => '',
            'REHOV_MOSER'            => '',
            'MISPAR_BAIT_MOSER'      => 0,
            'koma_MOSER'             => '',
            'MEKABEL'                => $shippingAddress->getName(),
            'HEVRA_MEKABEL'          => $shippingAddress->getCompany() ?? '',
            'TEL_MEKABEL'            => preg_replace('/[^0-9]/', '', $shippingAddress->getTelephone()),
            'EZOR_MEKABEL'           => 0,
            'SHM_EIR_MEKABEL'        => $shippingAddress->getCity(),
            'REHOV_MEKABEL'          => $street,
            'MISPAR_BAIT_MEKABEL'    => '',
            'koma_MEKABEL'           => '',
            'SUG_SHLIHUT'            => $shippingType,
            'HEAROT'                 => '',
            'SHEM_MAZMIN'            => '',
            'MICROSOFT_ORDER_NUMBER' => $order->getIncrementId(),
            'HEAROT_LKTOVET_MKOR'    => '',
            'HEAROT_LKTOVET_YAAD'    => '',
            'SHEM_CHEVRA'            => '',
            'TEOR_TKALA'             => '',
            'KARTONIM'               => '',
        ];

        $response = $this->doRequest($data);
        return $this->parseResponse($response, $order);
    }

    /**
     * @param Response $response
     * @param Order $order
     * @return string
     */
    protected function parseResponse($response, $order)
    {
        $tracking = '';
        $code     = $response->getStatusCode();
        if ($code == 200) {
            try {
                $xml   = str_replace('xmlns="Zigzag"', '', $response->getBody()->getContents());
                $sxe   = new SimpleXMLElement($xml, LIBXML_NOWARNING);
                $value = (string)$sxe;
                if (strlen($value) > 4) {
                    $tracking = $value;
                    $this->_eventManager->dispatch('zigzag_shipment_created', ['order' => $order, 'tracking_number' => $tracking]);
                } else {
                    $code = print_r($value, true);
                    $msg  = "Error Code Response for Insert Shipping to ZigZag\nResponse From ZigZag: $code";
                    $this->_helper->log('error', $msg, null, true);
                }
            } catch (Exception $e) {
                $msg = "Error Parsing Response for Insert Shipping to ZigZag\nError Code: {$e->getCode()}\nError Message: {$e->getMessage()}\nOrder Number: {$order->getIncrementId()}";
                $this->_helper->log('error', $msg, null, true);
            }
        } else {
            $reason = $response->getReasonPhrase();
            $msg    = "Error Getting Response for Insert Shipping to ZigZag\nError Code: $code\nReason: $reason\nOrder Number: {$order->getIncrementId()}";
            $this->_helper->log('error', $msg, null, true);
        }

        return $tracking;
    }
}