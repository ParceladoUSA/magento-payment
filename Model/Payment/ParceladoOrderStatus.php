<?php
namespace Parceladousa\Payment\Model\Payment;

class ParceladoOrderStatus extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'parcelado_order_status';

	const STATUS_OPEN     = 'open';
	const STATUS_PENDING  = 'pending';
	const STATUS_ANALYSIS = 'analysis';
	const STATUS_APPROVED = 'approved';
	const STATUS_CANCELED = 'canceled';
	const STATUS_ABORTED  = 'aborted';
	const STATUS_DELIVERED  = 'delivered';

	/**
	 * Return status Parcelado
	 *
	 * @var array
	 */
	const CODES_STATUS_AUTHORIZED = [self::STATUS_ANALYSIS, self::STATUS_PENDING];
	const CODES_STATUS_ABORTED    = [self::STATUS_CANCELED, self::STATUS_ABORTED];

	protected $_cacheTag = 'parcelado_order_status';

	protected $_eventPrefix = 'parcelado_order_status';

	protected function _construct()
	{
		$this->_init('Parceladousa\Payment\Model\ResourceModel\Payment\ParceladoOrderStatus');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}