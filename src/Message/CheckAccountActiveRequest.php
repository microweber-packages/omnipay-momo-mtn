<?php

namespace Omnipay\MoMoMtn\Message;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Check Account Active Request
 *
 * Checks if a specified account is active and registered for mobile money.
 */
class CheckAccountActiveRequest extends AbstractMoMoRequest
{
    /**
     * Get account holder type
     *
     * @return string
     */
    public function getAccountHolderType()
    {
        return $this->getParameter('accountHolderType') ?: 'MSISDN';
    }

    /**
     * Set account holder type
     *
     * @param string $value
     * @return $this
     */
    public function setAccountHolderType($value)
    {
        return $this->setParameter('accountHolderType', $value);
    }

    /**
     * Get account holder ID
     *
     * @return string
     */
    public function getAccountHolderId()
    {
        return $this->getParameter('accountHolderId');
    }

    /**
     * Set account holder ID
     *
     * @param string $value
     * @return $this
     */
    public function setAccountHolderId($value)
    {
        return $this->setParameter('accountHolderId', $value);
    }

    /**
     * Get the data for this request
     *
     * @return array
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('accountHolderId');
        
        // Validate required authentication parameters
        $this->validate('apiUserId', 'apiKey', 'subscriptionKey');

        return [];
    }

    /**
     * Send the request with specified data
     *
     * @param array $data The data to send
     * @return CheckAccountActiveResponse
     */
    public function sendData($data)
    {
        // Get access token
        try {
            $accessToken = $this->getAccessToken();
        } catch (\Exception $e) {
            return $this->response = new CheckAccountActiveResponse($this, $e->getMessage(), 401);
        }

        // Format account holder ID (clean phone number)
        $accountHolderId = $this->formatPhoneNumber($this->getAccountHolderId());
        $accountHolderType = $this->getAccountHolderType();

        // Prepare headers
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'X-Target-Environment' => $this->getTargetEnvironment(),
            'Ocp-Apim-Subscription-Key' => $this->getSubscriptionKey(),
        ];

        try {
            $httpResponse = $this->httpClient->request(
                'GET',
                $this->getBaseUrl() . '/collection/v1_0/accountholder/' . $accountHolderType . '/' . $accountHolderId . '/active',
                $headers
            );

            return $this->response = new CheckAccountActiveResponse($this, $httpResponse->getBody(), $httpResponse->getStatusCode());
        } catch (\Exception $e) {
            return $this->response = new CheckAccountActiveResponse($this, $e->getMessage(), 500);
        }
    }
}