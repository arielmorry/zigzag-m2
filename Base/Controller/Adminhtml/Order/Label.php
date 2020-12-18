<?php

namespace Zigzag\Base\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;

class Label extends Action implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var Raw
     */
    protected $_resultRaw;

    /**
     * @var ResultFactory
     */
    protected $_result;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ResultFactory $resultFactory
     * @param Raw $resultRaw
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ResultFactory $resultFactory,
        Raw $resultRaw
    )
    {
        parent::__construct($context);

        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultRaw         = $resultRaw;
        $this->_result            = $resultFactory;
    }

    /**
     * @return ResponseInterface|Raw|ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('ZigZag Shipping Label'));

        $result = $this->_result->create(ResultFactory::TYPE_LAYOUT);
        $html   = $result->getLayout()->createBlock('Zigzag\Base\Block\Adminhtml\Order\Label')->setTemplate('Zigzag_Base::label.phtml')->toHtml();
        $this->_resultRaw
            ->setHttpResponseCode(200)
            ->setHeader('Content-Type', 'text/html')
            ->setContents($html);
        return $this->_resultRaw;
    }
}
