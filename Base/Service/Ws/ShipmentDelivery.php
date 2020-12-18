<?php

namespace Zigzag\Base\Service\Ws;

use Magento\Sales\Model\Order;
use Zigzag\Base\Service\Base;
use SimpleXMLElement;
use Exception;

class ShipmentDelivery extends Base
{
    /**
     * Base URI for Vendor's Web Service
     */
    const WS_ENDPOINT = 'UpdateTaom';

    /**
     * @param Order $order
     * @param string $trackingNumber
     * @return bool|SimpleXMLElement[]
     */
    public function set($order, $trackingNumber = '')
    {

        $date = date('Y-m-d', strtotime($order->getZigzagDeliveryFrom()));
        $from = date('His', strtotime($order->getZigzagDeliveryFrom()));
        $to = date('His', strtotime($order->getZigzagDeliveryTo()));
        $data = [
            'Numerator' => $trackingNumber,
            'TaarichTeum' => $date,
            'FromTimeTeum' => $from,
            'ToTimeTeum' => $to,
        ];

        $response = $this->doRequest($data);
        return $this->parseResponse($response, $order);
    }

    /**
     * @param $response
     * @param Order $order
     * @return bool
     */
    protected function parseResponse($response, $order)
    {
        $result = false;
        $code = $response->getStatusCode();
        if ($code == 200) {
            try {
                $xml = str_replace('xmlns="Zigzag"', '', $response->getBody()->getContents());
                $sxe = new SimpleXMLElement($xml, LIBXML_NOWARNING);
                $value = (string)$sxe;
                if(is_numeric($value) && $value == 1) {
                    $result = true;
                } else {
                    $code = print_r($value, true);
                    $msg = "Error Update Shipping Delivery Date Time to ZigZag\nResponse From ZigZag: $code";
                    $this->_helper->log('error', $msg, null, true);
                }
            } catch (Exception $e) {
                $msg = "Error Parsing Response for Shipping Delivery Date Time to ZigZag\nError Code: {$e->getCode()}\nError Message: {$e->getMessage()}\nOrder Number: {$order->getIncrementId()}";
                $this->_helper->log('error', $msg, null, true);
            }
        } else {
            $reason = $response->getReasonPhrase();
            $msg    = "Error Getting Response for Shipping Delivery Date Time to ZigZag\nError Code: $code\nReason: $reason\nOrder Number: {$order->getIncrementId()}";
            $this->_helper->log('error', $msg, null, true);
        }

        return $result;
    }
}