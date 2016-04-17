<?php

namespace ShopGo\Paytabs\Model\System\Config\Source\Status;

use Magento\Framework\Option\ArrayInterface;

/**
 * Order Statuses source model
 */
class Status  implements ArrayInterface
{
    const PENDING     = \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT;
    const PROCESSING  = \Magento\Sales\Model\Order::STATE_PROCESSING;

    public function toOptionArray()
    {
        return [
            self::PENDING    => __('Pending Payment'),
            self::PROCESSING => __('Processing')
        ];
    }
}