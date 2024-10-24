<?php

namespace Parceladousa\Payment\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Parceladousa\Payment\Logger\Logger;
use Parceladousa\Payment\Model\Adminhtml\Source\ReferenceCurrency;
use Parceladousa\Payment\Model\Adminhtml\Source\ReferenceValue;

class Data extends AbstractHelper
{
    /** PRODUCTION */
    const PRODUCTION_API_URL = 'https://api.parceladousa.com/v1/';

    /** SANDBOX */
    const SANDBOX_API_URL = 'https://apisandbox.parceladousa.com/v1/';

    /** ACCESS TOKEN METHOD */
    const ACCESS_TOKEN_METHOD_URL = 'paymentapi/auth';

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
     * @param Logger $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface  $scopeConfig,
        Logger                $logger
    )
    {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
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
     * Return configured moment invoice order
     *
     * @return string
     */
    public function getMomentInvoiceOrder()
    {
        return $this->getConfigData('parcelado_payment', 'moment_invoice_order');
    }

    /**
     * Return configured reference value
     *
     * @return string
     */
    public function getReferenceValue()
    {
        return $this->getConfigData('parcelado_payment', 'reference_value');
    }

    /**
     * Return configured reference currency
     *
     * @return string
     */
    private function getReferenceCurrency()
    {
        return $this->getConfigData('parcelado_payment', 'reference_currency');
    }

    /**
     * @return string
     */
    public function getStoreCurrencyCode()
    {
        if($this->getReferenceCurrency() == ReferenceCurrency::CURRENTCURRENCY) {
            return $this->_storeManager->getStore()->getCurrentCurrencyCode();
        }

        return $this->_storeManager->getStore()->getBaseCurrencyCode();
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
    public function getParceladoAccessToken()
    {
        $pubKey = $this->getConfigData('parcelado_payment', 'merchant_key');
        $merchantCode = $this->getConfigData('parcelado_payment', 'merchant_id');

        $rawData = ['pubKey' => $pubKey, 'merchantCode' => $merchantCode];
        $headers = ['Content-Type:application/json'];
        $request = $this->curl('POST', self::ACCESS_TOKEN_METHOD_URL, $headers, json_encode($rawData));

        if ($request->http == 200) {

            return $request->body->token;

        } else {
            $this->logger->error('Unable to get Parcelado API access token!');
            $this->logger->error(json_encode($rawData));
            $this->logger->error(json_encode($request->http));
            $this->logger->error(json_encode($request->body));
            return null;
        }
    }

    public function curl($method, $endpoint, $headers = [], $postfilds = null)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->getParceladoRequestUrl() . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $postfilds,
        ));

        $responseData = curl_exec($curl);
        $response = new \stdClass();

        if (curl_errno($curl)) {
            $response->error = curl_error($curl);
            return $this;
        }

        $response->http = curl_getinfo($curl)['http_code'];
        $response->body = json_decode($responseData);
        curl_close($curl);

        return $response;
    }
}
