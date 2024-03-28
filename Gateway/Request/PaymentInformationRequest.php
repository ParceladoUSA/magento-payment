<?php

namespace Parceladousa\Payment\Gateway\Request;

use InvalidArgumentException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class PaymentInformationRequest implements BuilderInterface
{
    /** Order Identification Number */
    const MERCHANT_ORDER_ID = 'MerchantOrderId';

    /** Block Payment */
    const PAYMENT = 'Payment';

    /** Order Amount */
    const AMOUNT = 'Amount';

    public function build(array $buildSubject)
    {
        if (
            !isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];
        /** @var Order */
        $order = $payment->getPayment()->getOrder();
        $result = [];

        $result = [
            self::MERCHANT_ORDER_ID => $order->getIncrementId(),
            self::PAYMENT => [
                self::AMOUNT => $order->getGrandTotal()
            ]
        ];

        return $result;
    }
}