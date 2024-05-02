<?php

namespace Parceladousa\Payment\Controller\Checkout;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Model\Order;
use Parceladousa\Payment\Model\Payment\ParceladoOrderStatusFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Parceladousa\Payment\Model\Payment\ParceladoOrderStatus;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Parceladousa\Payment\Helper\Data;

class Parcelado implements \Magento\Framework\App\ActionInterface
{
    /** @var ParceladoOrderStatusFactory */
    private $_parceladoStatusFactory;

    /** @var OrderRepositoryInterface */
    private $_orderRepository;

    /** @var ResultFactory */
    protected $_resultFactory;

    /** @var Http */
    protected $_http;

    /** @var Cart */
    protected $cart;

    /**
     * @var Data
     */
    private $_helperData;

    public function __construct(
        Http $request,
        ResultFactory $resultFactory,
        OrderRepositoryInterface $orderRepository,
        ParceladoOrderStatusFactory $parceladoStatusFactory,
        Data $helperData
    ) {
        $this->_http = $request;
        $this->_resultFactory = $resultFactory;
        $this->_orderRepository = $orderRepository;
        $this->_parceladoStatusFactory = $parceladoStatusFactory;
        $this->_helperData = $helperData;
    }

    public function execute()
    {
        // Coloca o id da ordem da Parcelado em uma variável
        $orderId = $this->_http->getParam('orderId');

        $model = $this->_parceladoStatusFactory->create();
        $collection = $model->getCollection();
        $parceladoOrderStatus = $collection->addFieldToFilter('parcelado_order_id', ['eq' => $orderId])->getFirstItem();

        $accessToken = $this->_helperData->getParceladoAccessToken();

        $headers = [
            'Content-Type:application/json',
            "Authorization:Bearer $accessToken"
        ];
        $request = $this->_helperData->curl('GET', "paymentapi/order/{$orderId}", $headers);
        $response = $request->body;
        $model->setData(['id' => $parceladoOrderStatus->getId(), 'status' => $response->status]);
        $model->save();

        $collection = $model->getCollection();
        $parceladoOrderStatus = $collection->addFieldToFilter('parcelado_order_id', ['eq' => $orderId])->getFirstItem();

        $order = $this->_orderRepository->get($parceladoOrderStatus->getOrderId());

        $resultRedirect = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (in_array($parceladoOrderStatus->getStatus(), ParceladoOrderStatus::CODES_STATUS_AUTHORIZED)) {
            $resultRedirect->setPath('checkout/onepage/success');
        } else if (in_array($parceladoOrderStatus->getStatus(), ParceladoOrderStatus::CODES_STATUS_ABORTED)) {
            $order->setStatus(Order::STATE_CANCELED);
            $order->setState(Order::STATE_CANCELED);
            $order->save($order);
            $resultRedirect->setPath('sales/order/history');
        } else {
            $order->setStatus(ParceladoOrderStatus::STATUS_PENDING);
            $order->setState(ParceladoOrderStatus::STATUS_PENDING);
            $order->save($order);
            $resultRedirect->setPath('sales/order/history');
        }

        return $resultRedirect;
    }
}
