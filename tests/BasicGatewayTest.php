<?php

namespace Omnipay\MoMoMtn\Tests;

use Omnipay\Omnipay;
use Omnipay\MoMoMtn\Gateway;
use PHPUnit\Framework\TestCase;

/**
 * Basic Gateway Test
 * Testing basic functionality before full test suite
 */
class BasicGatewayTest extends TestCase
{
    /**
     * @var Gateway
     */
    protected $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = Omnipay::create('MoMoMtn');
    }

    public function testGatewayCanBeCreated()
    {
        $this->assertInstanceOf(Gateway::class, $this->gateway);
        $this->assertEquals('MTN Mobile Money', $this->gateway->getName());
    }

    public function testGatewayDefaultParameters()
    {
        $defaults = $this->gateway->getDefaultParameters();
        $this->assertArrayHasKey('apiUserId', $defaults);
        $this->assertArrayHasKey('subscriptionKey', $defaults);
        $this->assertArrayHasKey('targetEnvironment', $defaults);
    }

    public function testParameterSettersAndGetters()
    {
        $this->gateway->setApiUserId('test-user-id');
        $this->assertEquals('test-user-id', $this->gateway->getApiUserId());

        $this->gateway->setApiKey('test-api-key');
        $this->assertEquals('test-api-key', $this->gateway->getApiKey());

        $this->gateway->setSubscriptionKey('test-subscription-key');
        $this->assertEquals('test-subscription-key', $this->gateway->getSubscriptionKey());

        $this->gateway->setTargetEnvironment('production');
        $this->assertEquals('production', $this->gateway->getTargetEnvironment());
    }
}