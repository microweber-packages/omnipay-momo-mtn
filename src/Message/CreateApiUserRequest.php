<?php

namespace Omnipay\MoMoMtn\Message;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Create API User Request (Sandbox only)
 *
 * Creates a new API user in the sandbox environment for testing purposes.
 * This corresponds to test case TC02-01.
 */
class CreateApiUserRequest extends AbstractMoMoRequest
{
    /**
     * Get the data for this request
     *
     * @return array
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('subscriptionKey');

        $callbackHost = $this->getCallbackHost() ?: 'webhook.site';

        return [
            'providerCallbackHost' => $callbackHost
        ];
    }

    /**
     * Send the request with specified data
     *
     * @param array $data The data to send
     * @return CreateApiUserResponse
     * @throws InvalidRequestException
     */
    public function sendData($data)
    {
        if ($this->getTargetEnvironment() === 'production') {
            throw new InvalidRequestException('API User creation is only available in sandbox environment');
        }

        // Generate new UUID for API User ID
        $apiUserId = $this->generateUuid();

        $headers = [
            'Content-Type' => 'application/json',
            'X-Reference-Id' => $apiUserId,
            'Ocp-Apim-Subscription-Key' => $this->getSubscriptionKey(),
        ];

        try {
            $httpResponse = $this->httpClient->request(
                'POST',
                $this->getBaseUrl() . '/v1_0/apiuser',
                $headers,
                json_encode($data)
            );

            return $this->response = new CreateApiUserResponse($this, $httpResponse->getBody(), $httpResponse->getStatusCode(), $apiUserId);
        } catch (\Exception $e) {
            return $this->response = new CreateApiUserResponse($this, $e->getMessage(), 500);
        }
    }
}