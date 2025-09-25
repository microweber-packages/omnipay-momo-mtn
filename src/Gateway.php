<?php

namespace Omnipay\MoMoMtn;

use Omnipay\Common\AbstractGateway;
use Omnipay\MoMoMtn\Message\PurchaseRequest;
use Omnipay\MoMoMtn\Message\PurchaseResponse;
use Omnipay\MoMoMtn\Message\CompletePurchaseRequest;
use Omnipay\MoMoMtn\Message\CreateApiUserRequest;
use Omnipay\MoMoMtn\Message\CreateApiKeyRequest;
use Omnipay\MoMoMtn\Message\CreateTokenRequest;
use Omnipay\MoMoMtn\Message\CheckBalanceRequest;
use Omnipay\MoMoMtn\Message\CheckAccountActiveRequest;

/**
 * MTN Mobile Money Gateway
 *
 * This gateway provides integration with MTN Mobile Money API for payment processing.
 * It supports both sandbox and production environments.
 *
 * Example:
 *
 * <code>
 *   // Create a gateway for the MTN MoMo driver
 *   // (routes to GatewayFactory::create)
 *   $gateway = Omnipay::create('MoMoMtn');
 *
 *   // Initialize the gateway
 *   $gateway->setApiUserId('your-api-user-id');
 *   $gateway->setApiKey('your-api-key');
 *   $gateway->setSubscriptionKey('your-subscription-key');
 *   $gateway->setTargetEnvironment('sandbox'); // or 'production'
 *   $gateway->setCallbackHost('webhook.site'); // for sandbox
 *
 *   // Create API credentials (sandbox only)
 *   $response = $gateway->createApiUser([
 *       'subscriptionKey' => 'your-subscription-key',
 *       'callbackHost' => 'webhook.site'
 *   ])->send();
 *
 *   // Process payment
 *   $response = $gateway->purchase([
 *       'amount' => '10.00',
 *       'currency' => 'EUR',
 *       'payerPhone' => '46733123453',
 *       'payerMessage' => 'Payment for order #123',
 *       'payeeNote' => 'Order payment'
 *   ])->send();
 *
 *   if ($response->isSuccessful()) {
 *       echo "Payment ID: " . $response->getTransactionReference();
 *   }
 * </code>
 */
class Gateway extends AbstractGateway
{
    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     */
    public function getName()
    {
        return 'MTN Mobile Money';
    }

    /**
     * Define gateway parameters, in the following format:
     *
     * array(
     *     'username' => '', // string variable
     *     'testMode' => false, // boolean variable
     *     'landingPage' => array('billing', 'login'), // enum variable, first item is default
     * );
     */
    public function getDefaultParameters()
    {
        return [
            'apiUserId' => '',
            'apiKey' => '',
            'subscriptionKey' => '',
            'targetEnvironment' => 'sandbox',
            'callbackHost' => 'webhook.site',
            'testMode' => true,
        ];
    }

    /**
     * Get API User ID
     *
     * @return string
     */
    public function getApiUserId()
    {
        return $this->getParameter('apiUserId');
    }

    /**
     * Set API User ID
     *
     * @param string $value
     * @return $this
     */
    public function setApiUserId($value)
    {
        return $this->setParameter('apiUserId', $value);
    }

    /**
     * Get API Key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    /**
     * Set API Key
     *
     * @param string $value
     * @return $this
     */
    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    /**
     * Get Subscription Key
     *
     * @return string
     */
    public function getSubscriptionKey()
    {
        return $this->getParameter('subscriptionKey');
    }

    /**
     * Set Subscription Key
     *
     * @param string $value
     * @return $this
     */
    public function setSubscriptionKey($value)
    {
        return $this->setParameter('subscriptionKey', $value);
    }

    /**
     * Get Target Environment
     *
     * @return string
     */
    public function getTargetEnvironment()
    {
        return $this->getParameter('targetEnvironment');
    }

    /**
     * Set Target Environment
     *
     * @param string $value
     * @return $this
     */
    public function setTargetEnvironment($value)
    {
        return $this->setParameter('targetEnvironment', $value);
    }

    /**
     * Get Callback Host
     *
     * @return string
     */
    public function getCallbackHost()
    {
        return $this->getParameter('callbackHost');
    }

    /**
     * Set Callback Host
     *
     * @param string $value
     * @return $this
     */
    public function setCallbackHost($value)
    {
        return $this->setParameter('callbackHost', $value);
    }

    /**
     * Create API User (Sandbox only)
     *
     * @param array $parameters
     * @return CreateApiUserRequest
     */
    public function createApiUser(array $parameters = [])
    {
        return $this->createRequest(CreateApiUserRequest::class, $parameters);
    }

    /**
     * Create API Key (Sandbox only)
     *
     * @param array $parameters
     * @return CreateApiKeyRequest
     */
    public function createApiKey(array $parameters = [])
    {
        return $this->createRequest(CreateApiKeyRequest::class, $parameters);
    }

    /**
     * Create OAuth Token
     *
     * @param array $parameters
     * @return CreateTokenRequest
     */
    public function createToken(array $parameters = [])
    {
        return $this->createRequest(CreateTokenRequest::class, $parameters);
    }

    /**
     * Purchase request
     *
     * @param array $parameters
     * @return PurchaseRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(PurchaseRequest::class, $parameters);
    }

    /**
     * Complete purchase request
     *
     * @param array $parameters
     * @return CompletePurchaseRequest
     */
    public function completePurchase(array $parameters = [])
    {
        return $this->createRequest(CompletePurchaseRequest::class, $parameters);
    }

    /**
     * Check account balance
     *
     * @param array $parameters
     * @return CheckBalanceRequest
     */
    public function checkBalance(array $parameters = [])
    {
        return $this->createRequest(CheckBalanceRequest::class, $parameters);
    }

    /**
     * Check if account is active
     *
     * @param array $parameters
     * @return CheckAccountActiveRequest
     */
    public function checkAccountActive(array $parameters = [])
    {
        return $this->createRequest(CheckAccountActiveRequest::class, $parameters);
    }
}