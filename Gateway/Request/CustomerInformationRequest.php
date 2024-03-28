<?php

namespace Parceladousa\Payment\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order;

/**
 * Class CustomerInformationRequest - Customer Structure
 */
class CustomerInformationRequest implements BuilderInterface {

  /**
   * Customer block name.
   */
  const CUSTOMER = 'Customer';

  /** 
   * Customer Name
   */
  const NAME = 'Name';

  /**
   * Document to identify customer
   */
  const IDENTIFY = 'Identity';

  /**
   * Type of customer document
   */
  const IDENTIFY_TYPE = 'IdentityType';

  /** 
   * Customer Birthdate
   */
  const BIRTHDATE = 'Birthdate';

  /** 
   * Customer Email
   */
  const EMAIL = 'Email';

  public function build(array $buildSubject) {

    if (
      !isset($buildSubject['payment'])
      || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
    ) {
      throw new \InvalidArgumentException('Payment data object should be provided');
    }
    /** @var PaymentDataObjectInterface $payment */
    $payment = $buildSubject['payment'];

    /** @var Order */
    $order = $payment->getPayment()->getOrder();

    $result = [];

    $result = [
      self::CUSTOMER => [
        self::NAME => "{$order->getCustomerFirstname()} {$order->getCustomerLastname()}",
        self::EMAIL => "{$order->getCustomerEmail()}",
        self::BIRTHDATE => "{$order->getCustomerDob()}"
      ]
    ];
   
    return $result;
  }
}
