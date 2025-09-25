<?php

namespace Omnipay\MoMoMtn\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * Check Account Active Response
 *
 * Response for account active check operations.
 */
class CheckAccountActiveResponse extends AbstractResponse
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
        return $this->statusCode === 200;
    }

    /**
     * Is the account active?
     *
     * @return boolean
     */
    public function isAccountActive()
    {
        $data = $this->parseResponseData();
        return isset($data['result']) ? $data['result'] === true : false;
    }

    /**
     * Get the response message
     *
     * @return string|null
     */
    public function getMessage()
    {
        if ($this->isSuccessful()) {
            return $this->isAccountActive() ? 'Account is active' : 'Account is not active';
        }

        // Handle error cases
        switch ($this->statusCode) {
            case 400:
                return 'Bad Request - Invalid account holder information';
            case 401:
                return 'Unauthorized - Invalid credentials';
            case 403:
                return 'Forbidden - Access denied';
            case 404:
                return 'Not Found - Invalid target environment';
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

                return 'Account active check failed';
        }
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
     * Get the status code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->statusCode;
    }
}