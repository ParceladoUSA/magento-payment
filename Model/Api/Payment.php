<?php

namespace Parceladousa\Payment\Model\Api;

use Parceladousa\Payment\Api\PaymentInterface;
use Parceladousa\Payment\Model\Payment\ParceladoOrderStatusFactory;
use Magento\Framework\Serialize\Serializer\Json as JsonHelper;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Store\Model\StoreManagerInterface;
use Parceladousa\Payment\Helper\Data as PaymentHelper;
use Parceladousa\Payment\Model\Payment\ParceladoOrderStatus;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class Payment
 */
class Payment implements PaymentInterface
{
    /** @var ParceladoOrderStatusFactory */
    private $_parceladoStatusFactory;

    /** @var JsonHelper */
    private $_jsonHelper;

    /** @var StoreManagerInterface */
    private $_storeManager;

    /** @var ZendClientFactory */
    private $_httpClientFactory;

    /** @var PaymentHelper */
    private $_helper;

    /** @var OrderRepositoryInterface */
    private $_orderRepositoryInterface;

    /**
     * Payment api constructor
     *
     * @param ParceladoOrderStatusFactory $parceladoStatusFactory
     * @param JsonHelper $jsonHelper
     * @param ZendClientFactory $httpClientFactory
     * @param StoreManagerInterface $storeManager
     * @param PaymentHelper $helper
     * @param OrderRepositoryInterface $orderRepositoryInterface
     */
    public function __construct(
        ParceladoOrderStatusFactory $parceladoStatusFactory,
        JsonHelper $jsonHelper,
        ZendClientFactory $httpClientFactory,
        StoreManagerInterface $storeManager,
        PaymentHelper $helper,
        OrderRepositoryInterface $orderRepositoryInterface
    ) {
        $this->_parceladoStatusFactory = $parceladoStatusFactory;
        $this->_jsonHelper = $jsonHelper;
        $this->_httpClientFactory = $httpClientFactory;
        $this->_storeManager = $storeManager;
        $this->_helper = $helper;
        $this->_orderRepositoryInterface = $orderRepositoryInterface;
    }

    /**
     * Starts parcelado payment
     *
     * @param string $customerData
     * @return string parcelado api link
     */
    public function startPayment($customerData, $billingData, $totals)
    {
        $customerData = $this->_jsonHelper->unserialize($customerData);
        $billingData = $this->_jsonHelper->unserialize($billingData);
        $totals = $this->_jsonHelper->unserialize($totals);

        try {
            $client = $this->_httpClientFactory->create();

            $url = $this->_helper->getParceladoRequestUrl();

            $accessToken = $this->_helper->getParceladoAccessToken();
            $currency = $this->_storeManager->getStore()->getCurrentCurrencyCode();
            $currency = (empty($currency) || ($currency != 'USD' || $currency != 'BRL'))? 'USD' : $currency;
            $rawData = ['amount' => $totals['grand_total'], 'currency' => $currency];

            $clientData = [
                'name' => $customerData['firstname'] . ' ' . $customerData['lastname'],
                'email' => $customerData['email'],
                'phone' => $billingData['telephone'],
                'doc' => $customerData['taxvat'],
                'cep' => $billingData['postcode'],
                'address' => $billingData['street'][0],
                'addressNumber' => $billingData['street'][1] ?? '',
                'district' => $billingData['district'] ?? '',
                'city' => $billingData['city'],
                'state' => $billingData['regionCode']
            ];

            $rawData['client'] = $clientData;

            $rawData['callback'] = $this->_storeManager->getStore()->getBaseUrl() . 'payment/checkout/parcelado';

            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer $accessToken"
            ];

            $client->setUri($url . 'paymentapi/order');
            $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
            $client->setHeaders($headers);
            $client->setRawData(json_encode($rawData), 'application/json');
            $client->setMethod(ZendClient::POST);

            $responseBody = $client->request()->getBody();

            $response = json_decode($responseBody, true);

            $model = $this->_parceladoStatusFactory->create();

            $collection = $model->getCollection();

            $parceladoOrderStatus = $collection->addFieldToFilter('customer_id', ['eq' => $customerData['id']])
                ->addFieldToFilter('parcelado_order_id', ['eq' => 'PARCELADOAPI'])
                ->setOrder('created_at', 'DESC')
                ->getFirstItem();


            $model->setData([
                'id' => $parceladoOrderStatus->getId(),
                'parcelado_order_id' => $response['data']['orderId'],
                'status' => "open"
            ]);
            $model->save();

            return json_encode(['url' => $response['data']['url'], 'parcelado_order_id' => $response['data']['orderId']]);
        } catch (\Exception $e) {
            $this->_helpererror("Error on generate url".$e->getMessage());
        }
        return '';
    }

    /**
     * Update payment status by webhook from parcelado api
     *
     * @param [type] $orderId
     * @param [type] $status
     * @return void
     */
    public function updatePayment($orderId, $status)
    {
        try {
            $model = $this->_parceladoStatusFactory->create();

            $collection = $model->getCollection();

            $parceladoOrderStatus = $collection->addFieldToFilter('parcelado_order_id', ['eq' => $orderId])->getFirstItem();

            $model->setData(['id' => $parceladoOrderStatus->getId(), 'status' => $status]);
            $model->save();

            $this->_helper->logger->info("newHook ".json_encode([$orderId, $status]));

            if (!empty($parceladoOrderStatus->getOrderId())) {

                $order = $this->_orderRepositoryInterface->get($parceladoOrderStatus->getOrderId());

                if ($status == ParceladoOrderStatus::STATUS_DELIVERED) {
                    $order->setStatus(Order::STATE_PROCESSING);
                    $order->setState(Order::STATE_PROCESSING);
                } else if (in_array($status, ParceladoOrderStatus::CODES_STATUS_ABORTED)) {
                    $order->setStatus(Order::STATE_CANCELED);
                    $order->setState(Order::STATE_CANCELED);
                }

                $this->_orderRepositoryInterface->save($order);
            }
        } catch (\Exception $e) {
            $this->_helper->logger->error('Unable to update order status!' . $e->getMessage());
        }
    }
}
