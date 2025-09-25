<?php

namespace Omnipay\MoMoMtn\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * Create API Key Response
 *
 * Response for API Key creation in sandbox environment.
 */
class CreateApiKeyResponse extends AbstractResponse
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
        return $this->statusCode === 201;
    }

    /**
     * Get the created API Key
     *
     * @return string|null
     */
    public function getApiKey()
    {
        $data = $this->parseResponseData();
        return isset($data['apiKey']) ? $data['apiKey'] : null;
    }

    /**
     * Get the response message
     *
     * @return string|null
     */
    public function getMessage()
    {
        if ($this->isSuccessful()) {
            return 'API Key created successfully';
        }

        if (is_string($this->data)) {
            return $this->data;
        }

        $data = $this->parseResponseData();
        if (isset($data['message'])) {
            return $data['message'];
        }

        return 'API Key creation failed';
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