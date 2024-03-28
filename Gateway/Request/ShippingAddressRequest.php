<?php

namespace Parceladousa\Payment\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order\Address;

/**
 * Class ShippingAddressRequest - Customer Structure
 */
class ShippingAddressRequest implements BuilderInterface {

  /**
   * Customer block name.
   */
  const CUSTOMER = 'Customer';

  /** 
   * Address block name
   */
  const ADDRESS = 'Address';

  /** 
   * Customer Street
   */
  const STREET = 'Street';

  /**
   * Address Number
   */
  const NUMBER = 'Number';

  /**
   * Type of customer document
   */
  const COMPLEMENT = 'Complement';

  /** 
   * Customer Zipcode
   */
  const ZIPCODE = 'Zipcode';

  /** 
   * Customer District
   */
  const DISTRICT = 'District';

  /**
   * Customer City
   */
  const CITY = 'City';

  /** 
   * Customer State
   */
  const STATE = 'State';

  /**
   * Customer Country
   */
  const COUNTRY = 'Country';

  public function build(array $buildSubject) {
    if (
      !isset($buildSubject['payment'])
      || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
    ) {
      throw new \InvalidArgumentException('Payment data object should be provided');
    }

    /** @var PaymentDataObjectInterface $payment */
    $payment = $buildSubject['payment'];
    $order = $payment->getPayment()->getOrder();

    $shippingAddress = $order->getShippingAddress();
    $billingAddress = $order->getBillingAddress();
    if (isset($shippingAddress)) {
      $address = $this->buildAddress($shippingAddress);
    } else if (isset($billingAddress)) {
      $address = $this->buildAddress($billingAddress);
    }

    $result = [];

    $result = [
      self::CUSTOMER => [
        self::ADDRESS => $address
      ]
    ];

    return $result;
  }

  /**
   * Build array of customer address information
   * 
   * @param OrderAddressInterface|Address $addressInformation
   * @return array
   */
  private function buildAddress($addressInformation) {
    $address = $addressInformation->getStreet();

    if (count($address) == 4) {
      $street = (isset($address[0]) ? $address[0] : '..');
      $number = (isset($address[1]) ? $address[1] : '..');
      $complement = (isset($address[2]) ? $address[2] : '..');
      $district = (isset($address[3]) ? $address[3] : '..');
    } else {
      $street = (isset($address[0]) ? $address[0] : '..');
      $number = (isset($address[1]) ? $address[1] : '..');
      $district = (isset($address[2]) ? $address[2] : '..');
      $complement = '';
    }

    $postcode = preg_replace("/[^0-9]/", "", $addressInformation->getPostcode());
    $city = $addressInformation->getCity();
    $state = $addressInformation->getRegionCode();
    $country = $addressInformation->getCountryId();

    return [
      self::STREET => $street,
      self::NUMBER => $number,
      self::COMPLEMENT => $complement,
      self::ZIPCODE => $postcode,
      self::DISTRICT => $district,
      self::CITY => $city,
      self::STATE => $state,
      self::COUNTRY => $country
    ];
  }
}
