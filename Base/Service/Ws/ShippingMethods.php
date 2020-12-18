<?php

namespace Zigzag\Base\Service\Ws;

use Zigzag\Base\Service\Base;
use SimpleXMLElement;
use Exception;

class ShippingMethods extends Base
{
    /**
     * Base URI for Vendor's Web Service
     */
    const WS_ENDPOINT = 'GetSugeyShlihuyot';

    /**
     * @return array|SimpleXMLElement[]
     */
    public function get()
    {
        $response = $this->doRequest();
        return $this->parseResponse($response);
    }

    /**
     * @param $response
     * @return array|SimpleXMLElement[]
     */
    protected function parseResponse($response)
    {
        $options = [];
        $code     = $response->getStatusCode();
        if ($code == 200) {
            try {
                $xml = str_replace('xmlns="Zigzag"', '', $response->getBody()->getContents());
                $sxe    = new SimpleXMLElement($xml, LIBXML_NOWARNING);
                $sxe->registerXPathNamespace('d', 'urn:schemas-microsoft-com:xml-diffgram-v1');
                $tables = $sxe->xpath('//Table');
                foreach ($tables as $t) {
                    $options[(int)$t->KOD] = (string)$t->TEUR;
                }
            } catch (Exception $e) {
                $msg = "Error Parsing Response for Shipping Types from ZigZag\nError Code: {$e->getCode()}\nError Message: {$e->getMessage()}";
                $this->_helper->log('error', $msg, null, true);
            }
        } else {
            $reason = $response->getReasonPhrase();
            $msg    = "Error Getting Response for Shipping Types from ZigZag\nError Code: $code\nReason: $reason";
            $this->_helper->log('error', $msg, null, true);
        }

        return $options;
    }
}