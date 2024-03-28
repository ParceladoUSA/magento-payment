<?php

namespace Parceladousa\Payment\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Environment implements OptionSourceInterface {

  const SANDBOX = 'sandbox';
  const PRODUCTION = 'production';

  public function toOptionArray() {
    return [
      [
        'value' => self::PRODUCTION,
        'label' => __('Production')
      ],
      [
        'value' =>  self::SANDBOX,
        'label' => __('Sandbox')
      ]
    ];
  }
}
