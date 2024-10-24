<?php

namespace Parceladousa\Payment\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Parceladousa\Payment\Model\Payment\ParceladoOrderStatus;

class MomentInvoiceOrder implements OptionSourceInterface {

    const DELIVERED = ParceladoOrderStatus::STATUS_DELIVERED;
    const APPROVED = ParceladoOrderStatus::STATUS_APPROVED;

    public function toOptionArray() {
        return [
            [
                'value' => self::DELIVERED,
                'label' => __('Ao ser Transferido')
            ],
            [
                'value' =>  self::APPROVED,
                'label' => __('Ao ser Liberado')
            ]
        ];
    }
}
