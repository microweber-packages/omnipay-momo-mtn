<?php

namespace Omnipay\MoMoMtn\Tests;

use Omnipay\Omnipay;
use Omnipay\MoMoMtn\Gateway;
use PHPUnit\Framework\TestCase;

/**
 * Authentication Test Suite
 * Testing authentication flow step by step
 */
class AuthenticationTest extends TestCase
{
    /**
     * @var Gateway
     */
    protected $gateway;

    /**
     * @var array
     */
    protected $validCredentials;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = Omnipay::create('MoMoMtn');

        // Use working credentials from the old tests and provided keys
        $this->validCredentials = [
            'apiUserId' => '2bf15487-2309-46e8-82e9-1f658cf3a82c',
            'apiKey' => '7f00bd3a51d7485cbbd85d083e70481b',
            'subscriptionKey' => 'b95263cce7184eaba10d1d309ded4d59', // Primary key from user
            'targetEnvironment' => 'sandbox',
            'testMode' => true
        ];
    }

    /**
     * Test TC02-05: Valid credentials should generate token
     */
    public function testTC02_05_ValidCredentialsToken()
    {
        echo "\n=== TC02-05: Testing valid credentials ===\n";
        
        try {
            $response = $this->gateway->createToken([
                'apiUserId' => $this->validCredentials['apiUserId'],
                'apiKey' => $this->validCredentials['apiKey'],
                'subscriptionKey' => $this->validCredentials['subscriptionKey'],
                'targetEnvironment' => 'sandbox'
            ])->send();

            if ($response->isSuccessful()) {
                $token = $response->getAccessToken();
                echo "✅ SUCCESS: Token generated - " . substr($token, 0, 20) . "...\n";
                echo "Result for CSV: OK - Token generated successfully\n";
                $this->assertTrue($response->isSuccessful());
                $this->assertNotEmpty($token);
            } else {
                echo "❌ FAILED: " . $response->getMessage() . "\n";
                echo "Result for CSV: FAIL - " . $response->getMessage() . "\n";
                $this->fail('Token generation failed: ' . $response->getMessage());
            }
        } catch (\Exception $e) {
            echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
            echo "Result for CSV: FAIL - Exception: " . $e->getMessage() . "\n";
            $this->fail('Exception during token generation: ' . $e->getMessage());
        }
    }

    /**
     * Test TC02-03: Invalid subscription key should fail
     */
    public function testTC02_03_InvalidSubscriptionKey()
    {
        echo "\n=== TC02-03: Testing invalid subscription key ===\n";
        
        try {
            $response = $this->gateway->createToken([
                'apiUserId' => $this->validCredentials['apiUserId'],
                'apiKey' => $this->validCredentials['apiKey'],
                'subscriptionKey' => 'invalid_subscription_key_12345',
                'targetEnvironment' => 'sandbox'
            ])->send();

            if (!$response->isSuccessful()) {
                echo "✅ SUCCESS: Correctly rejected invalid subscription key\n";
                echo "Result for CSV: OK - Correctly rejected invalid subscription key\n";
                $this->assertFalse($response->isSuccessful());
            } else {
                echo "❌ FAILED: Should have rejected invalid subscription key\n";
                echo "Result for CSV: FAIL - Should have rejected invalid subscription key\n";
                $this->fail('Should have rejected invalid subscription key');
            }
        } catch (\Exception $e) {
            echo "✅ SUCCESS: Exception thrown for invalid key - " . $e->getMessage() . "\n";
            echo "Result for CSV: OK - Exception thrown for invalid key\n";
            $this->assertTrue(true, 'Expected exception for invalid subscription key');
        }
    }

    /**
     * Test TC02-04: Invalid API key should fail
     */
    public function testTC02_04_InvalidAPIKey()
    {
        echo "\n=== TC02-04: Testing invalid API key ===\n";
        
        try {
            $response = $this->gateway->createToken([
                'apiUserId' => 'invalid-api-user-id',
                'apiKey' => 'invalid_api_key',
                'subscriptionKey' => $this->validCredentials['subscriptionKey'],
                'targetEnvironment' => 'sandbox'
            ])->send();

            if (!$response->isSuccessful()) {
                echo "✅ SUCCESS: Correctly rejected invalid API key\n";
                echo "Result for CSV: OK - Correctly rejected invalid API key\n";
                $this->assertFalse($response->isSuccessful());
            } else {
                echo "❌ FAILED: Should have rejected invalid API key\n";
                echo "Result for CSV: FAIL - Should have rejected invalid API key\n";
                $this->fail('Should have rejected invalid API key');
            }
        } catch (\Exception $e) {
            echo "✅ SUCCESS: Exception thrown for invalid API key - " . $e->getMessage() . "\n";
            echo "Result for CSV: OK - Exception thrown for invalid API key\n";
            $this->assertTrue(true, 'Expected exception for invalid API key');
        }
    }
}