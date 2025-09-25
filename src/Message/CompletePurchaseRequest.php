<?php

namespace Omnipay\MoMoMtn\Message;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Complete Purchase Request (GET RequestToPay Status)
 *
 * Retrieves the status of a RequestToPay transaction.
 * This corresponds to test cases TC02-12 to TC02-16.
 */
class CompletePurchaseRequest extends AbstractMoMoRequest
{
    /**
     * Get the data for this request
     *
     * @return array
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('transactionReference');
        
        // Validate required authentication parameters
        $this->validate('apiUserId', 'apiKey', 'subscriptionKey');

        return [];
    }

    /**
     * Send the request with specified data
     *
     * @param array $data The data to send
     * @return CompletePurchaseResponse
     */
    public function sendData($data)
    {
        $referenceId = $this->getTransactionReference();

        // Get access token
        try {
            $accessToken = $this->getAccessToken();
        } catch (\Exception $e) {
            return $this->response = new CompletePurchaseResponse($this, $e->getMessage(), 401);
        }

        // Prepare headers
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'X-Target-Environment' => $this->getTargetEnvironment(),
            'Ocp-Apim-Subscription-Key' => $this->getSubscriptionKey(),
        ];

        try {
            $httpResponse = $this->httpClient->request(
                'GET',
                $this->getBaseUrl() . '/collection/v1_0/requesttopay/' . $referenceId,
                $headers
            );

            return $this->response = new CompletePurchaseResponse($this, $httpResponse->getBody(), $httpResponse->getStatusCode());
        } catch (\Exception $e) {
            return $this->response = new CompletePurchaseResponse($this, $e->getMessage(), 500);
        }
    }
}