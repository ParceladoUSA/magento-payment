<?php

namespace Parceladousa\Payment\Observer;

use Parceladousa\Payment\Model\Payment\ParceladoOrderStatusFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Parceladousa\Payment\Helper\Data;

class OrderObserver implements \Magento\Framework\Event\ObserverInterface
{
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
        Data $helperData
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_parceladoStatusFactory = $parceladoStatusFactory;
        $this->_helperData = $helperData;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();

    	    $model = $this->_parceladoStatusFactory->create();
    	    $collection = $model->getCollection();
            $parceladoOrderStatus = $collection->addFieldToFilter('order_id', ['eq' => $order->getId()])->getFirstItem();
            if(empty($parceladoOrderStatus->getId())){
                    $model = $this->_parceladoStatusFactory->create();
                    $model->addData(['customer_id' => $order->getCustomerId(), 'order_id' => $order->getId(), 'status' => 'open', 'parcelado_order_id' => 'PARCELADOAPI']);
                    $model->save();

                    $this->_helperData->logger->info("New order created!".json_encode($model->getData()));
            }
        } catch (\Exception $e) {
            $this->_helperData->logger->error("Error on create order observer!".$e->getMessage());
        }
    }
}
