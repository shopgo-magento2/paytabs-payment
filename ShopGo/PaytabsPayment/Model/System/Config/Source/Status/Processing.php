<?php

namespace Shopgo\PaytabsPayment\Model\System\Config\Source\Status;

/**
 * Order Statuses source model
 */
class Processing extends \Magento\Sales\Model\Config\Source\Order\Status
{
    /**
     * @var string
     */
    protected $_stateStatuses = \Magento\Sales\Model\Order::STATE_PROCESSING;
}