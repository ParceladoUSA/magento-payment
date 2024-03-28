<?php

namespace Parceladousa\Payment\Model\ResourceModel\Payment\ParceladoOrderStatus;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	public function _construct()
	{
		$this->_init("Parceladousa\Payment\Model\Payment\ParceladoOrderStatus", "Parceladousa\Payment\Model\ResourceModel\Payment\ParceladoOrderStatus");
	}
}
