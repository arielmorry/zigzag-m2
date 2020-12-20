<?php

namespace Zigzag\Base\Plugin\View\Element\UiComponent\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory as BaseCollectionFactory;
use Magento\Framework\Data\Collection;

class CollectionFactory
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * CollectionFactory constructor.
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    )
    {
        $this->_request = $request;
    }

    /**
     * Get report collection
     *
     * @param BaseCollectionFactory $subject
     * @param Collection $collection
     * @param string $requestName
     * @return Collection|void
     */
    public function afterGetReport(BaseCollectionFactory $subject, $collection, $requestName)
    {
        if ($requestName == 'sales_order_grid_data_source') {
            $separator = '<br>';
            $action    = $this->_request->getActionName();
            if ($action == 'gridToCsv' || $action == 'gridToXml') {
                $separator = '|';
            }

            /** @var Select $select */
            $select = $collection->getSelect();
            $select->joinLeft(
                ['shipment_track' => $collection->getTable('sales_shipment_track')],
                'main_table.entity_id = shipment_track.order_id',
                array('shipment_track' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT shipment_track.track_number SEPARATOR "' . $separator . '")'))
            );
            $select->group('main_table.entity_id');
        }

        return $collection;
    }
}