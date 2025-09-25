<?php

namespace Omnipay\MoMoMtn\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

/**
 * Purchase Response
 *
 * Response for RequestToPay operations.
 */
class PurchaseResponse extends AbstractResponse
{
    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var string|null
     */
    protected $referenceId;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param mixed $data
     * @param int $statusCode
     * @param string|null $referenceId
     */
    public function __construct(RequestInterface $request, $data, $statusCode = 200, $referenceId = null)
    {
        parent::__construct($request, $data);
        $this->statusCode = $statusCode;
        $this->referenceId = $referenceId;
    }

    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return $this->statusCode === 202;
    }

    /**
     * Is the transaction pending?
     * 
     * For RequestToPay, a 202 status means the request was accepted 
     * and is being processed (pending user approval).
     *
     * @return boolean
     */
    public function isPending()
    {
        return $this->isSuccessful();
    }

    /**
     * Get the transaction reference (Reference ID)
     *
     * @return string|null
     */
    public function getTransactionReference()
    {
        return $this->referenceId;
    }

    /**
     * Get the transaction ID (same as reference)
     *
     * @return string|null
     */
    public function getTransactionId()
    {
        return $this->getTransactionReference();
    }

    /**
     * Get the response message
     *
     * @return string|null
     */
    public function getMessage()
    {
        if ($this->isSuccessful()) {
            return 'Payment request sent successfully. Please check your phone for the payment prompt.';
        }

        // Handle error cases
        switch ($this->statusCode) {
            case 400:
                return 'Bad Request - Invalid request data';
            case 401:
                return 'Unauthorized - Invalid credentials';
            case 403:
                return 'Forbidden - Access denied';
            case 409:
                return 'Conflict - Duplicate reference ID';
            case 500:
                return 'Server Error - Please try again later';
            default:
                if (is_string($this->data)) {
                    return $this->data;
                }

                $data = json_decode($this->data, true);
                if (isset($data['message'])) {
                    return $data['message'];
                }

                return 'Payment request failed';
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

    /**
     * Get redirect URL (not applicable for mobile money)
     *
     * @return null
     */
    public function getRedirectUrl()
    {
        return null;
    }
}