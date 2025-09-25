<?php

namespace Omnipay\MoMoMtn\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * Create OAuth Token Response
 *
 * Response for OAuth token creation.
 */
class CreateTokenResponse extends AbstractResponse
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
     * Get the access token
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        $data = $this->getData();
        return isset($data['access_token']) ? $data['access_token'] : null;
    }

    /**
     * Get parsed data from response
     *
     * @return array
     */
    public function getData()
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
     * Get the token type
     *
     * @return string|null
     */
    public function getTokenType()
    {
        $data = $this->getData();
        return isset($data['token_type']) ? $data['token_type'] : null;
    }

    /**
     * Get the token expiry time in seconds
     *
     * @return int|null
     */
    public function getExpiresIn()
    {
        $data = $this->getData();
        return isset($data['expires_in']) ? $data['expires_in'] : null;
    }

    /**
     * Get the response message
     *
     * @return string|null
     */
    public function getMessage()
    {
        if ($this->isSuccessful()) {
            return 'OAuth token created successfully';
        }

        if (is_string($this->data)) {
            return $this->data;
        }

        $data = json_decode($this->data, true);
        if (isset($data['message'])) {
            return $data['message'];
        }

        return 'Token creation failed';
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