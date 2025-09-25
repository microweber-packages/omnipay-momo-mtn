<?php

namespace Omnipay\MoMoMtn\Message;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Create API Key Request (Sandbox only)
 *
 * Creates an API key for an existing API user in the sandbox environment.
 * This corresponds to test case TC02-02.
 */
class CreateApiKeyRequest extends AbstractMoMoRequest
{
    /**
     * Get the data for this request
     *
     * @return array
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('apiUserId', 'subscriptionKey');

        return [];
    }

    /**
     * Send the request with specified data
     *
     * @param array $data The data to send
     * @return CreateApiKeyResponse
     * @throws InvalidRequestException
     */
    public function sendData($data)
    {
        if ($this->getTargetEnvironment() === 'production') {
            throw new InvalidRequestException('API Key creation is only available in sandbox environment');
        }

        $headers = [
            'Ocp-Apim-Subscription-Key' => $this->getSubscriptionKey(),
        ];

        try {
            $httpResponse = $this->httpClient->request(
                'POST',
                $this->getBaseUrl() . '/v1_0/apiuser/' . $this->getApiUserId() . '/apikey',
                $headers
            );

            return $this->response = new CreateApiKeyResponse($this, $httpResponse->getBody(), $httpResponse->getStatusCode());
        } catch (\Exception $e) {
            return $this->response = new CreateApiKeyResponse($this, $e->getMessage(), 500);
        }
    }
}