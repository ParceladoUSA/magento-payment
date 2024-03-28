<?php
namespace Parceladousa\Payment\Api;

interface PaymentInterface
{
    /**
     * * Starts payment on parcelado api
     *
     * @param string $customerData
     * @param string $billingData
     * @param string $totals
     * @return string
     */
    public function startPayment($customerData, $billingData, $totals);

    /**
     * Verify if payment process is done
     *
     * @param int $orderId
     * @param string $status
     * @return void
     */
    public function updatePayment($orderId, $status);    
}
