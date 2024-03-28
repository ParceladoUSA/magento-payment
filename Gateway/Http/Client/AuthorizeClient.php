<?php

namespace Parceladousa\Payment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Class AuthorizeClient - Returns authorization for payment.
 */
class AuthorizeClient implements ClientInterface
{
    /**
     * Places request to gateway.
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        return ['success' => TRUE];
    }
}
