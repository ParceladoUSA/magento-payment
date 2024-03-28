<?php
namespace Parceladousa\Payment\Model\ResourceModel\Payment;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ParceladoOrderStatus extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('parcelado_order_status', 'id');
    }
}
