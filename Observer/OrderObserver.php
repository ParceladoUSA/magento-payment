<?php

namespace Parceladousa\Payment\Observer;

use Parceladousa\Payment\Model\Payment\ParceladoOrderStatusFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Parceladousa\Payment\Model\Payment\ParceladoOrderStatus;
use Magento\Sales\Model\Order;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Parceladousa\Payment\Helper\Data;

class OrderObserver implements \Magento\Framework\Event\ObserverInterface
{
    /** 
     * @var ZendClientFactory 
     */
    protected $httpClientFactory;

    /** 
     * @var Data 
     */
    private $_helperData;

    /**
     * @var OrderRepositoryInterface
     */
    private $_orderRepository;

    /**
     * @var ParceladoOrderStatusFactory
     */
    private $_parceladoStatusFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ParceladoOrderStatusFactory $parceladoStatusFactory,
        ZendClientFactory $httpClientFactory,
        Data $helperData
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_parceladoStatusFactory = $parceladoStatusFactory;
        $this->httpClientFactory = $httpClientFactory;
        $this->_helperData = $helperData;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();

            $model = $this->_parceladoStatusFactory->create();
            $model->addData(['customer_id' => $order->getCustomerId(), 'order_id' => $order->getId(), 'status' => 'open', 'parcelado_order_id' => 'PARCELADOAPI']);
            $model->save();

            $this->_helperData->logger->info("New order created!");

        } catch (\Exception $e) {
            $this->_helperData->logger->error("Error on create order observer!".$e->getMessage());
        }
    }
}
