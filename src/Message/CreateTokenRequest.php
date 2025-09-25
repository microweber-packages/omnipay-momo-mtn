<?php

namespace Omnipay\MoMoMtn\Message;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Create OAuth Token Request
 *
 * Creates an OAuth access token for API authentication.
 * This corresponds to test cases TC02-03 to TC02-06.
 */
class CreateTokenRequest extends AbstractMoMoRequest
{
    /**
     * Get the data for this request
     *
     * @return array
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('apiUserId', 'apiKey', 'subscriptionKey');

        return [];
    }

    /**
     * Send the request with specified data
     *
     * @param array $data The data to send
     * @return CreateTokenResponse
     */
    public function sendData($data)
    {
        // Create basic auth header
        $credentials = base64_encode($this->getApiUserId() . ':' . $this->getApiKey());

        $headers = [
            'Authorization' => 'Basic ' . $credentials,
            'Ocp-Apim-Subscription-Key' => $this->getSubscriptionKey(),
        ];

        try {
            $httpResponse = $this->httpClient->request(
                'POST',
                $this->getBaseUrl() . '/collection/token/',
                $headers
            );

            return $this->response = new CreateTokenResponse($this, $httpResponse->getBody(), $httpResponse->getStatusCode());
        } catch (\Exception $e) {
            return $this->response = new CreateTokenResponse($this, $e->getMessage(), 500);
        }
    }
}