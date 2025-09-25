<?php

namespace Omnipay\MoMoMtn\Message;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Purchase Request (RequestToPay)
 *
 * Initiates a payment request to a mobile money subscriber.
 * This corresponds to test cases TC02-01 to TC02-11.
 */
class PurchaseRequest extends AbstractMoMoRequest
{
    /**
     * Get payer phone number
     *
     * @return string
     */
    public function getPayerPhone()
    {
        return $this->getParameter('payerPhone');
    }

    /**
     * Set payer phone number
     *
     * @param string $value
     * @return $this
     */
    public function setPayerPhone($value)
    {
        return $this->setParameter('payerPhone', $value);
    }

    /**
     * Get payer message
     *
     * @return string
     */
    public function getPayerMessage()
    {
        return $this->getParameter('payerMessage');
    }

    /**
     * Set payer message
     *
     * @param string $value
     * @return $this
     */
    public function setPayerMessage($value)
    {
        return $this->setParameter('payerMessage', $value);
    }

    /**
     * Get payee note
     *
     * @return string
     */
    public function getPayeeNote()
    {
        return $this->getParameter('payeeNote');
    }

    /**
     * Set payee note
     *
     * @param string $value
     * @return $this
     */
    public function setPayeeNote($value)
    {
        return $this->setParameter('payeeNote', $value);
    }

    /**
     * Get external ID
     *
     * @return string
     */
    public function getExternalId()
    {
        return $this->getParameter('externalId');
    }

    /**
     * Set external ID
     *
     * @param string $value
     * @return $this
     */
    public function setExternalId($value)
    {
        return $this->setParameter('externalId', $value);
    }

    /**
     * Get callback URL
     *
     * @return string
     */
    public function getCallbackUrl()
    {
        return $this->getParameter('callbackUrl');
    }

    /**
     * Set callback URL
     *
     * @param string $value
     * @return $this
     */
    public function setCallbackUrl($value)
    {
        return $this->setParameter('callbackUrl', $value);
    }

    /**
     * Get the data for this request
     *
     * @return array
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('amount', 'payerPhone');

        // Validate required authentication parameters
        $this->validate('apiUserId', 'apiKey', 'subscriptionKey');

        $amount = $this->getAmount();
        $currency = $this->getCurrency() ?: 'EUR';
        $payerPhone = $this->formatPhoneNumber($this->getPayerPhone());

        // Validate amount is positive
        $numericAmount = floatval($amount);
        if ($numericAmount <= 0) {
            throw new InvalidRequestException('Amount must be positive');
        }
        
        // Use decimal amount directly (no rounding)
        $finalAmount = (string)$numericAmount;

        return [
            'amount' => $finalAmount,
            'currency' => $currency,
            'externalId' => $this->getExternalId() ?: $this->generateUuid(),
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => $payerPhone
            ],
            'payerMessage' => $this->getPayerMessage() ?: 'Payment request',
            'payeeNote' => $this->getPayeeNote() ?: 'Payment for order'
        ];
    }

    /**
     * Send the request with specified data
     *
     * @param array $data The data to send
     * @return PurchaseResponse
     */
    public function sendData($data)
    {
        // Generate reference ID for this transaction
        $referenceId = $this->generateUuid();

        // Get access token
        try {
            $accessToken = $this->getAccessToken();
        } catch (\Exception $e) {
            return $this->response = new PurchaseResponse($this, $e->getMessage(), 401);
        }

        // Prepare headers
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'X-Reference-Id' => $referenceId,
            'X-Target-Environment' => $this->getTargetEnvironment(),
            'Ocp-Apim-Subscription-Key' => $this->getSubscriptionKey(),
            'Content-Type' => 'application/json'
        ];

        // Add callback URL if provided
        if ($this->getCallbackUrl()) {
            $headers['X-Callback-Url'] = $this->getCallbackUrl();
        }

        try {
            $httpResponse = $this->httpClient->request(
                'POST',
                $this->getBaseUrl() . '/collection/v1_0/requesttopay',
                $headers,
                json_encode($data)
            );

            return $this->response = new PurchaseResponse($this, $httpResponse->getBody(), $httpResponse->getStatusCode(), $referenceId);
        } catch (\Exception $e) {
            return $this->response = new PurchaseResponse($this, $e->getMessage(), 500);
        }
    }
}