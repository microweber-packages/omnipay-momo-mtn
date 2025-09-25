<?php

namespace Omnipay\MoMoMtn\Message;

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Abstract Request
 *
 * This is the parent class for all MTN Mobile Money requests.
 */
abstract class AbstractMoMoRequest extends AbstractRequest
{
    /**
     * Live Endpoint URL
     *
     * @var string
     */
    protected $liveEndpoint = 'https://momodeveloper.mtn.com';

    /**
     * Test Endpoint URL
     *
     * @var string
     */
    protected $testEndpoint = 'https://sandbox.momodeveloper.mtn.com';

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
     * Get the base URL for API requests
     *
     * @return string
     */
    protected function getBaseUrl()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }

    /**
     * Get test mode
     *
     * @return bool
     */
    public function getTestMode()
    {
        $environment = $this->getTargetEnvironment();
        return $environment === 'sandbox' || parent::getTestMode();
    }

    /**
     * Get common headers for API requests
     *
     * @return array
     */
    protected function getHeaders()
    {
        return [
            'Content-Type' => 'application/json',
            'Ocp-Apim-Subscription-Key' => $this->getSubscriptionKey(),
            'X-Target-Environment' => $this->getTargetEnvironment(),
        ];
    }

    /**
     * Get OAuth token for authenticated requests
     *
     * @return string|null
     * @throws InvalidRequestException
     */
    protected function getAccessToken()
    {
        $apiUserId = $this->getApiUserId();
        $apiKey = $this->getApiKey();
        $subscriptionKey = $this->getSubscriptionKey();

        if (!$apiUserId || !$apiKey || !$subscriptionKey) {
            throw new InvalidRequestException('API credentials are required');
        }

        // Create basic auth header
        $credentials = base64_encode($apiUserId . ':' . $apiKey);

        // Make token request
        $response = $this->httpClient->request(
            'POST',
            $this->getBaseUrl() . '/collection/token/',
            [
                'Authorization' => 'Basic ' . $credentials,
                'Ocp-Apim-Subscription-Key' => $subscriptionKey
            ]
        );

        $responseBody = $response->getBody();
        $data = json_decode($responseBody->getContents(), true);

        if (!isset($data['access_token'])) {
            throw new InvalidRequestException('Failed to obtain access token');
        }

        return $data['access_token'];
    }

    /**
     * Generate UUID for reference IDs
     *
     * @return string
     */
    protected function generateUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Format phone number to international format
     *
     * @param string $phone
     * @return string
     * @throws InvalidRequestException
     */
    protected function formatPhoneNumber($phone)
    {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) < 10) {
            throw new InvalidRequestException('Phone number too short. Must be in international format.');
        }
        
        return $phone;
    }
}