<?php

namespace Parceladousa\Payment\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ReferenceCurrency implements OptionSourceInterface {

    const BASECURRENCY = 'baseCurrencyCode';
    const CURRENTCURRENCY = 'currentCurrencyCode';
    const DEFAULTCURRENCY = 'defaultCurrencyCode';

    public function toOptionArray() {
        return [
            [
                'value' => self::BASECURRENCY,
                'label' => __('Moeda Base')
            ],
            [
                'value' =>  self::CURRENTCURRENCY,
                'label' => __('Moeda Corrente')
            ],
            [
                'value' =>  self::DEFAULTCURRENCY,
                'label' => __('Moeda Padrão')
            ]
        ];
    }
}
