<?php

namespace Parceladousa\Payment\Model\Config;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Parceladousa\Payment\Helper\Data;

class Settings implements ConfigProviderInterface
{
    const CODE = 'parcelado_payment';

    const CHECKOUT_CONFIG_CODE = 'parcelado';
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var SessionFactory
     */
    private $_sessionFactory;

    /**
     * @var Data
     */
    private $_helper;

    /**
     * Settings constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param SessionFactory $sessionFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        SessionFactory $sessionFactory,
        Data $helper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_sessionFactory = $sessionFactory;
        $this->_helper = $helper;
    }

    /**
     * @param $xmlPath
     * @return mixed
     */
    protected function getValue($xmlPath)
    {
        return $this->scopeConfig->getValue($xmlPath, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getLogo()
    {
        return '';
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function getConfig()
    {
        /**
         * @var Session $customer
         */
        $customer = $this->_sessionFactory->create();
        return [
            'payment' => [
                self::CHECKOUT_CONFIG_CODE => [
                    'enable' => $this->_helper->getEnabledForStores(self::CODE) && $customer->getCustomerId(),
                    'logo' => $this->getLogo(),
                ]
            ]
        ];
    }
}