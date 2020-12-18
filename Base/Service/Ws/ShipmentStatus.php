<?php

namespace Zigzag\Base\Service\Ws;

use Zigzag\Base\Service\Base;
use SimpleXMLElement;
use Exception;
use GuzzleHttp\Psr7\Response;

class ShipmentStatus extends Base
{
    /**
     * Base URI for Vendor's Web Service
     */
    const WS_ENDPOINT = 'getStatusShlihutYomiAndHistoriaByNum';

    /**
     * @param string $trackingNumber
     * @return array|SimpleXMLElement[]
     */
    public function get($trackingNumber = '')
    {
        $data = [
            'NUMERATOR_ZIGZAG' => $trackingNumber
        ];

        $response = $this->doRequest($data);
        return $this->parseResponse($response, $trackingNumber);
    }

    /**
     * @param Response $response
     * @param string $trackingNumber
     * @return array|SimpleXMLElement[]
     */
    protected function parseResponse($response, $trackingNumber)
    {
        $tables = [];
        $code = $response->getStatusCode();
        if ($code == 200) {
            try {
                $xml = str_replace('xmlns="Zigzag"', '', $response->getBody()->getContents());
                $sxe    = new SimpleXMLElement($xml, LIBXML_NOWARNING);
                $sxe->registerXPathNamespace('d', 'urn:schemas-microsoft-com:xml-diffgram-v1');
                $tables = $sxe->xpath('//Table');
                if (!$tables) {
                    $msg = "Error Empty Shipping Status from ZigZag\nTracking Number: $trackingNumber";
                    $this->_helper->log('error', $msg, null, true);
                }
            } catch (Exception $e) {
                $msg = "Error Parsing Response for Shipping Status from ZigZag\nError Code: {$e->getCode()}\nError Message: {$e->getMessage()}\nTracking Number: $trackingNumber";
                $this->_helper->log('error', $msg, null, true);
            }
        } else {
            $reason = $response->getReasonPhrase();
            $msg    = "Error Getting Response for Shipping Status from ZigZag\nError Code: $code\nReason: $reason\nTracking Number: $trackingNumber";
            $this->_helper->log('error', $msg, null, true);
        }

        return $tables;
    }
}