<?php

namespace Zigzag\Base\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use Zigzag\Base\Helper\Data;
use Zigzag\Base\Model\Carrier\Zigzag;
use Magento\Framework\Event\ManagerInterface as EventManager;

class Base
{
    /**
     * Base URI for Vendor's Web Service
     */
    const WS_BASE_URI = 'https://api.zig-zag.co.il/ZigZag_WS3/Service.asmx/';

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var ResponseFactory
     */
    protected $_responseFactory;

    /**
     * @var ClientFactory
     */
    protected $_clientFactory;

    /**
     * @var EventManager
     */
    protected $_eventManager;

    /**
     * Base constructor.
     * @param Data $helper
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param EventManager $eventManager
     */
    public function __construct(
        Data $helper,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        EventManager $eventManager
    )
    {
        $this->_helper          = $helper;
        $this->_clientFactory   = $clientFactory;
        $this->_responseFactory = $responseFactory;
        $this->_eventManager    = $eventManager;
    }

    /**
     * @param array $params
     * @param string $requestMethod
     * @return Response
     */
    protected function doRequest(
        array $params = [],
        string $requestMethod = Request::HTTP_METHOD_POST): Response
    {
        $credentials = [
            'UserName' => $this->_helper->getConfig(Zigzag::ZIGZAG_SHIPPING_USERNAME_PATH),
            'Password' => $this->_helper->getConfig(Zigzag::ZIGZAG_SHIPPING_PASSWORD_PATH),
        ];

        $data = array_merge($credentials, $params);

        /** @var Client $client */
        $client = $this->_clientFactory->create(['config' => [
            'base_uri' => self::WS_BASE_URI
        ]]);

        $options = [
            'debug'       => false,
            'verify'      => false,
            'form_params' => $data
        ];

        if ($requestMethod == Request::HTTP_METHOD_GET) {
            unset($options['form_params']);
            $options = ['query' => $data];
        }

        try {
            /** @var Response $response */
            $response = $client->request(
                $requestMethod,
                static::WS_ENDPOINT,
                $options
            );
        } catch (GuzzleException $e) {
            /** @var Response $response */
            $response = $this->_responseFactory->create([
                'status' => $e->getCode(),
                'reason' => $e->getMessage()
            ]);
            $msg      = "Error while making request to ZigZag";
            $this->_helper->log('error', $msg, $e, true);
        }

        return $response;
    }
}