<?php

namespace Omnipay\MoMoMtn\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * Complete Purchase Response
 *
 * Response for RequestToPay status check.
 */
class CompletePurchaseResponse extends AbstractResponse
{
    /**
     * @var int
     */
    protected $statusCode;

    /**
     * Constructor
     *
     * @param \Omnipay\Common\Message\RequestInterface $request
     * @param mixed $data
     * @param int $statusCode
     */
    public function __construct(\Omnipay\Common\Message\RequestInterface $request, $data, $statusCode = 200)
    {
        parent::__construct($request, $data);
        $this->statusCode = $statusCode;
    }

    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return $this->statusCode === 200 && $this->getStatus() === 'SUCCESSFUL';
    }

    /**
     * Is the transaction pending?
     *
     * @return boolean
     */
    public function isPending()
    {
        return $this->statusCode === 200 && $this->getStatus() === 'PENDING';
    }

    /**
     * Was the transaction rejected/failed?
     *
     * @return boolean
     */
    public function isRejected()
    {
        $status = $this->getStatus();
        return in_array($status, ['FAILED', 'REJECTED', 'TIMEOUT']);
    }

    /**
     * Get payment status
     *
     * @return string|null
     */
    public function getStatus()
    {
        $data = $this->parseResponseData();
        return isset($data['status']) ? strtoupper($data['status']) : null;
    }

    /**
     * Get the transaction reference
     *
     * @return string|null
     */
    public function getTransactionReference()
    {
        $data = $this->parseResponseData();
        // Return financial transaction ID if available, otherwise external ID
        return $data['financialTransactionId'] ?? $data['externalId'] ?? null;
    }

    /**
     * Get the transaction ID
     *
     * @return string|null
     */
    public function getTransactionId()
    {
        return $this->getTransactionReference();
    }

    /**
     * Get the amount
     *
     * @return string|null
     */
    public function getAmount()
    {
        $data = $this->parseResponseData();
        return isset($data['amount']) ? $data['amount'] : null;
    }

    /**
     * Get the currency
     *
     * @return string|null
     */
    public function getCurrency()
    {
        $data = $this->parseResponseData();
        return isset($data['currency']) ? $data['currency'] : null;
    }

    /**
     * Get payer information
     *
     * @return array|null
     */
    public function getPayer()
    {
        $data = $this->parseResponseData();
        return isset($data['payer']) ? $data['payer'] : null;
    }

    /**
     * Parse response data handling different data types
     *
     * @return array
     */
    protected function parseResponseData()
    {
        if (is_string($this->data)) {
            return json_decode($this->data, true) ?: [];
        } elseif (is_object($this->data) && method_exists($this->data, 'getContents')) {
            return json_decode($this->data->getContents(), true) ?: [];
        } elseif (is_array($this->data)) {
            return $this->data;
        }
        
        return [];
    }

    /**
     * Get the response message
     *
     * @return string|null
     */
    public function getMessage()
    {
        if ($this->isSuccessful()) {
            return 'Payment completed successfully';
        }

        if ($this->isPending()) {
            return 'Payment is still pending user approval';
        }

        if ($this->isRejected()) {
            $status = $this->getStatus();
            switch ($status) {
                case 'FAILED':
                    return 'Payment failed';
                case 'REJECTED':
                    return 'Payment was rejected by user';
                case 'TIMEOUT':
                    return 'Payment timed out';
                default:
                    return 'Payment was not completed';
            }
        }

        // Handle HTTP error cases
        switch ($this->statusCode) {
            case 400:
                return 'Bad Request - Invalid request data';
            case 401:
                return 'Unauthorized - Invalid credentials';
            case 404:
                return 'Not Found - Invalid reference ID';
            case 500:
                return 'Server Error - Please try again later';
            default:
                if (is_string($this->data)) {
                    return $this->data;
                }

                $data = $this->parseResponseData();
                if (isset($data['message'])) {
                    return $data['message'];
                }

                return 'Payment status check failed';
        }
    }

    /**
     * Get the status code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->statusCode;
    }
}