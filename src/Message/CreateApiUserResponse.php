<?php

namespace Omnipay\MoMoMtn\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

/**
 * Create API User Response
 *
 * Response for API User creation in sandbox environment.
 */
class CreateApiUserResponse extends AbstractResponse
{
    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var string|null
     */
    protected $apiUserId;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param mixed $data
     * @param int $statusCode
     * @param string|null $apiUserId
     */
    public function __construct(RequestInterface $request, $data, $statusCode = 200, $apiUserId = null)
    {
        parent::__construct($request, $data);
        $this->statusCode = $statusCode;
        $this->apiUserId = $apiUserId;
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
     * Get the created API User ID
     *
     * @return string|null
     */
    public function getApiUserId()
    {
        return $this->apiUserId;
    }

    /**
     * Get the response message
     *
     * @return string|null
     */
    public function getMessage()
    {
        if ($this->isSuccessful()) {
            return 'API User created successfully';
        }

        if (is_string($this->data)) {
            return $this->data;
        }

        $data = json_decode($this->data, true);
        if (isset($data['message'])) {
            return $data['message'];
        }

        return 'API User creation failed';
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