<?php

namespace Parceladousa\Payment\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Parceladousa\Payment\Logger\Logger;

class Data extends AbstractHelper
{
    /** PRODUCTION */
    const PRODUCTION_API_URL = 'https://api.parceladousa.com/v1/';

    /** SANDBOX */
    const SANDBOX_API_URL = 'https://apisandbox.parceladousa.com/v1/';

    /** ACCESS TOKEN METHOD */
    const ACCESS_TOKEN_METHOD_URL = 'paymentapi/auth';

    /** 
     * @var ZendClientFactory 
     */
    protected $httpClientFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Logger
     */
    public $logger;

    /**
     * Undocumented function
     *
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ZendClientFactory $httpClientFactory
     * @param Logger $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ZendClientFactory $httpClientFactory,
        Logger $logger
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->httpClientFactory = $httpClientFactory;
        $this->logger = $logger;
    }

    /**
     * @param $paymentMethodCode
     * @return bool
     */
    public function getEnabledForStores(string $payment_code)
    {
        $xmlConfigPath = "payment/" . $payment_code . "/active";
        try {
            $storeCode = $this->storeManager->getStore()->getCode();
        } catch (NoSuchEntityException $e) {
            return false;
        }
        return (bool)$this->scopeConfig->getValue($xmlConfigPath, ScopeInterface::SCOPE_STORE, $storeCode);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCurrentStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param string $code
     * @return false|string
     */
    public function getConfigData($code, $field)
    {
        //ADICIONAR MAIS 1 PARAMETRO STORE ID
        $path = 'payment/' . $code . '/' . $field;
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return configured environment
     * 
     * @return string
     */
    private function getEnvironment()
    {
        return $this->getConfigData('parcelado_payment', 'environment');
    }

    /**
     * Return Parcelado URL to send requests
     * 
     * @return string
     */
    public function getParceladoRequestUrl()
    {
        $environment = $this->getEnvironment();
        if ($environment === 'production') {
            return self::PRODUCTION_API_URL;
        } else if ($environment === 'sandbox') {
            return self::SANDBOX_API_URL;
        }
    }

    /**
     * Return Parcelado access token
     *
     * @return string
     */
    public function getParceladoAccessToken(): string
    {
        try {
            $client = $this->httpClientFactory->create();

            $url = $this->getParceladoRequestUrl();
            $pubKey = $this->getConfigData('parcelado_payment', 'merchant_key');
            $merchantCode = $this->getConfigData('parcelado_payment', 'merchant_id');

            $rawData = ['pubKey' => $pubKey, 'merchantCode' => $merchantCode];

            $headers = ['Content-Type' => 'application/json'];

            $client->setUri($url . self::ACCESS_TOKEN_METHOD_URL);
            $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
            $client->setHeaders($headers);
            $client->setRawData(json_encode($rawData), 'application/json');
            $client->setMethod(ZendClient::POST);

            $response = $client->request();

            $responseBody = json_decode($response->getBody());
            
            return $responseBody->token;
        } catch (\Exception $e) {
            $this->logger->error('Unable to get Parcelado API access token!' . $e->getMessage());
        }
    }
}
