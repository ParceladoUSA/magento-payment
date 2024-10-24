<?php

namespace Parceladousa\Payment\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ReferenceValue implements OptionSourceInterface {

    const GRANDTOTAL = 'grand_total';
    const BASEGRANDTOTAL = 'base_grand_total';

    public function toOptionArray() {
        return [
            [
                'value' => self::GRANDTOTAL,
                'label' => __('Total do pedido na moeda atual')
            ],
            [
                'value' =>  self::BASEGRANDTOTAL,
                'label' => __('Total do pedido na moeda base')
            ]
        ];
    }
}
