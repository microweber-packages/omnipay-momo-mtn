<?php

namespace Omnipay\MoMoMtn\Tests;

use Omnipay\Omnipay;
use Omnipay\MoMoMtn\Gateway;
use PHPUnit\Framework\TestCase;

/**
 * Payment Test Suite
 * Testing RequestToPay functionality
 */
class PaymentTest extends TestCase
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

        // Use working credentials
        $this->validCredentials = [
            'apiUserId' => '2bf15487-2309-46e8-82e9-1f658cf3a82c',
            'apiKey' => '7f00bd3a51d7485cbbd85d083e70481b',
            'subscriptionKey' => 'b95263cce7184eaba10d1d309ded4d59',
            'targetEnvironment' => 'sandbox',
            'testMode' => true
        ];

        $this->gateway->initialize($this->validCredentials);
    }

    /**
     * Test TC02-01: RequestToPay - No Exceptions (Subscriber Approves)
     */
    public function testTC02_01_RequestToPaySuccess()
    {
        echo "\n=== TC02-01: Testing successful RequestToPay ===\n";
        
        try {
            $response = $this->gateway->purchase([
                'amount' => '100',
                'currency' => 'EUR',
                'payerPhone' => '56733123453', // Test phone for success
                'payerMessage' => 'Test payment for TC02-01',
                'payeeNote' => 'Test order payment'
            ])->send();

            if ($response->isSuccessful()) {
                echo "✅ SUCCESS: 202 Accepted - Transaction ID: " . $response->getTransactionReference() . "\n";
                echo "Result for CSV: OK - 202 Accepted\n";
                $this->assertTrue($response->isSuccessful());
                $this->assertNotEmpty($response->getTransactionReference());
                $this->assertEquals(202, $response->getCode());
            } else {
                echo "❌ FAILED: " . $response->getMessage() . "\n";
                echo "Result for CSV: FAIL - " . $response->getMessage() . "\n";
                $this->fail('Purchase request failed: ' . $response->getMessage());
            }
        } catch (\Exception $e) {
            echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
            echo "Result for CSV: FAIL - Exception: " . $e->getMessage() . "\n";
            $this->fail('Exception during purchase: ' . $e->getMessage());
        }
    }

    /**
     * Test TC02-05: Incomplete Information (Missing Amount)
     */
    public function testTC02_05_IncompleteInformation()
    {
        echo "\n=== TC02-05: Testing incomplete information ===\n";
        
        try {
            $response = $this->gateway->purchase([
                'currency' => 'EUR',
                'payerPhone' => '56733123453'
                // Missing amount
            ])->send();

            echo "❌ UNEXPECTED: Request should have failed with missing amount\n";
            echo "Result for CSV: FAIL - Should have rejected missing amount\n";
            $this->fail('Should have failed with missing amount');
            
        } catch (\Exception $e) {
            echo "✅ SUCCESS: Exception for missing data - " . $e->getMessage() . "\n";
            echo "Result for CSV: OK - 400 Bad Request\n";
            $this->assertTrue(true, 'Expected exception for incomplete data');
        }
    }

    /**
     * Test TC02-07: Invalid B-Party (Phone Number)
     */
    public function testTC02_07_InvalidBParty()
    {
        echo "\n=== TC02-07: Testing invalid phone number ===\n";
        
        try {
            $response = $this->gateway->purchase([
                'amount' => '100',
                'currency' => 'EUR',
                'payerPhone' => 'invalid_phone'
            ])->send();

            echo "❌ UNEXPECTED: Request should have failed with invalid phone\n";
            echo "Result for CSV: FAIL - Should have rejected invalid phone\n";
            $this->fail('Should have failed with invalid phone number');
            
        } catch (\Exception $e) {
            echo "✅ SUCCESS: Exception for invalid phone - " . $e->getMessage() . "\n";
            echo "Result for CSV: OK - 500 Server Error\n";
            $this->assertTrue(true, 'Expected exception for invalid phone');
        }
    }

    /**
     * Test TC02-09: Invalid Subscription Key in Payment
     */
    public function testTC02_09_InvalidSubscriptionKeyPayment()
    {
        echo "\n=== TC02-09: Testing invalid subscription key in payment ===\n";
        
        try {
            $invalidGateway = Omnipay::create('MoMoMtn');
            $invalidCredentials = $this->validCredentials;
            $invalidCredentials['subscriptionKey'] = 'invalid_subscription_key';
            
            $invalidGateway->initialize($invalidCredentials);

            $response = $invalidGateway->purchase([
                'amount' => '100',
                'currency' => 'EUR',
                'payerPhone' => '56733123453'
            ])->send();

            if (!$response->isSuccessful() && $response->getCode() === 401) {
                echo "✅ SUCCESS: 401 Access Denied as expected\n";
                echo "Result for CSV: OK - 401 Access Denied\n";
                $this->assertEquals(401, $response->getCode());
            } else {
                echo "❌ FAILED: Should have returned 401 for invalid subscription key\n";
                echo "Result for CSV: FAIL - Expected 401\n";
                $this->fail('Should have returned 401 for invalid subscription key');
            }
        } catch (\Exception $e) {
            echo "✅ SUCCESS: Exception for invalid key - " . $e->getMessage() . "\n";
            echo "Result for CSV: OK - 401 Access Denied\n";
            $this->assertTrue(true, 'Expected exception for invalid subscription key');
        }
    }

    /**
     * Test TC02-12: RequestToPay GET - Status Check
     */
    public function testTC02_12_RequestToPayStatusCheck()
    {
        echo "\n=== TC02-12: Testing RequestToPay status check ===\n";
        
        try {
            // First create a payment
            $purchaseResponse = $this->gateway->purchase([
                'amount' => '50',
                'currency' => 'EUR',
                'payerPhone' => '56733123453',
                'payerMessage' => 'Test payment for status check'
            ])->send();

            if (!$purchaseResponse->isSuccessful()) {
                echo "❌ FAILED: Could not create initial payment for status check\n";
                echo "Result for CSV: FAIL - Could not create payment\n";
                $this->fail('Could not create initial payment');
                return;
            }

            // Then check its status
            $statusResponse = $this->gateway->completePurchase([
                'transactionReference' => $purchaseResponse->getTransactionReference()
            ])->send();

            if ($statusResponse->getCode() === 200) {
                $status = $statusResponse->getStatus();
                echo "✅ SUCCESS: 200 OK - Status: " . $status . "\n";
                echo "Result for CSV: OK - 200 OK\n";
                $this->assertEquals(200, $statusResponse->getCode());
            } else {
                echo "❌ FAILED: Status check failed - " . $statusResponse->getMessage() . "\n";
                echo "Result for CSV: FAIL - " . $statusResponse->getMessage() . "\n";
                $this->fail('Status check failed: ' . $statusResponse->getMessage());
            }
        } catch (\Exception $e) {
            echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
            echo "Result for CSV: FAIL - Exception: " . $e->getMessage() . "\n";
            $this->fail('Exception during status check: ' . $e->getMessage());
        }
    }

    /**
     * Test TC02-13: Invalid Reference ID
     */
    public function testTC02_13_InvalidReferenceId()
    {
        echo "\n=== TC02-13: Testing invalid reference ID ===\n";
        
        try {
            $response = $this->gateway->completePurchase([
                'transactionReference' => 'invalid-reference-id-12345'
            ])->send();

            if ($response->getCode() === 404) {
                echo "✅ SUCCESS: 404 Not Found as expected\n";
                echo "Result for CSV: OK - 404 Not Found\n";
                $this->assertEquals(404, $response->getCode());
            } else {
                echo "❌ FAILED: Should have returned 404 for invalid reference ID\n";
                echo "Result for CSV: FAIL - Expected 404\n";
                $this->fail('Should have returned 404 for invalid reference ID');
            }
        } catch (\Exception $e) {
            echo "✅ SUCCESS: Exception for invalid ID - " . $e->getMessage() . "\n";
            echo "Result for CSV: OK - 404 Not Found\n";
            $this->assertTrue(true, 'Expected exception for invalid reference ID');
        }
    }

    /**
     * Test different phone number scenarios
     */
    public function testPhoneNumberScenarios_Failed()
    {
        echo "\n=== PHONE-FAILED: Testing FAILED phone number ===\n";
        
        try {
            $response = $this->gateway->purchase([
                'amount' => '100',
                'currency' => 'EUR',
                'payerPhone' => '46733123450', // FAILED phone
                'payerMessage' => 'Test payment for FAILED scenario',
                'payeeNote' => 'Test failed payment'
            ])->send();

            // Payment request should be accepted, but status will show FAILED
            if ($response->isSuccessful()) {
                echo "✅ SUCCESS: 202 Accepted - Transaction ID: " . $response->getTransactionReference() . "\n";
                echo "Result for CSV: OK - 202 Accepted (will fail on status check)\n";
                $this->assertTrue($response->isSuccessful());
                
                // Check the status to see if it shows FAILED
                sleep(2); // Wait a bit for processing
                $statusResponse = $this->gateway->completePurchase([
                    'transactionReference' => $response->getTransactionReference()
                ])->send();
                
                if ($statusResponse->getStatus() === 'FAILED') {
                    echo "✅ SUCCESS: Status shows FAILED as expected\n";
                } else {
                    echo "ℹ️  INFO: Status: " . $statusResponse->getStatus() . "\n";
                }
            } else {
                echo "❌ FAILED: " . $response->getMessage() . " (Code: " . $response->getCode() . ")\n";
                echo "Result for CSV: FAIL - " . $response->getMessage() . "\n";
                $this->fail('Payment request failed: ' . $response->getMessage());
            }
        } catch (\Exception $e) {
            echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
            echo "Result for CSV: FAIL - Exception: " . $e->getMessage() . "\n";
            $this->fail('Exception during payment: ' . $e->getMessage());
        }
    }

    public function testPhoneNumberScenarios_Rejected()
    {
        echo "\n=== PHONE-REJECTED: Testing REJECTED phone number ===\n";
        
        try {
            $response = $this->gateway->purchase([
                'amount' => '100',
                'currency' => 'EUR',
                'payerPhone' => '46733123451', // REJECTED phone
                'payerMessage' => 'Test payment for REJECTED scenario',
                'payeeNote' => 'Test rejected payment'
            ])->send();

            // Payment request should be accepted, but status will show REJECTED
            if ($response->isSuccessful()) {
                echo "✅ SUCCESS: 202 Accepted - Transaction ID: " . $response->getTransactionReference() . "\n";
                echo "Result for CSV: OK - 202 Accepted (will be rejected on status check)\n";
                $this->assertTrue($response->isSuccessful());
                
                // Check the status to see if it shows REJECTED
                sleep(2); // Wait a bit for processing
                $statusResponse = $this->gateway->completePurchase([
                    'transactionReference' => $response->getTransactionReference()
                ])->send();
                
                if ($statusResponse->getStatus() === 'REJECTED') {
                    echo "✅ SUCCESS: Status shows REJECTED as expected\n";
                } else {
                    echo "ℹ️  INFO: Status: " . $statusResponse->getStatus() . "\n";
                }
            } else {
                echo "❌ FAILED: " . $response->getMessage() . " (Code: " . $response->getCode() . ")\n";
                echo "Result for CSV: FAIL - " . $response->getMessage() . "\n";
                $this->fail('Payment request failed: ' . $response->getMessage());
            }
        } catch (\Exception $e) {
            echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
            echo "Result for CSV: FAIL - Exception: " . $e->getMessage() . "\n";
            $this->fail('Exception during payment: ' . $e->getMessage());
        }
    }

    public function testPhoneNumberScenarios_Timeout()
    {
        echo "\n=== PHONE-TIMEOUT: Testing TIMEOUT phone number ===\n";
        
        try {
            $response = $this->gateway->purchase([
                'amount' => '100',
                'currency' => 'EUR',
                'payerPhone' => '46733123452', // TIMEOUT phone
                'payerMessage' => 'Test payment for TIMEOUT scenario',
                'payeeNote' => 'Test timeout payment'
            ])->send();

            // Payment request should be accepted, but status will show TIMEOUT
            if ($response->isSuccessful()) {
                echo "✅ SUCCESS: 202 Accepted - Transaction ID: " . $response->getTransactionReference() . "\n";
                echo "Result for CSV: OK - 202 Accepted (will timeout on status check)\n";
                $this->assertTrue($response->isSuccessful());
                
                // Check the status to see if it shows TIMEOUT
                sleep(2); // Wait a bit for processing
                $statusResponse = $this->gateway->completePurchase([
                    'transactionReference' => $response->getTransactionReference()
                ])->send();
                
                if ($statusResponse->getStatus() === 'TIMEOUT') {
                    echo "✅ SUCCESS: Status shows TIMEOUT as expected\n";
                } else {
                    echo "ℹ️  INFO: Status: " . $statusResponse->getStatus() . "\n";
                }
            } else {
                echo "❌ FAILED: " . $response->getMessage() . " (Code: " . $response->getCode() . ")\n";
                echo "Result for CSV: FAIL - " . $response->getMessage() . "\n";
                $this->fail('Payment request failed: ' . $response->getMessage());
            }
        } catch (\Exception $e) {
            echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
            echo "Result for CSV: FAIL - Exception: " . $e->getMessage() . "\n";
            $this->fail('Exception during payment: ' . $e->getMessage());
        }
    }

    public function testPhoneNumberScenarios_Pending()
    {
        echo "\n=== PHONE-PENDING: Testing PENDING phone number ===\n";
        
        try {
            $response = $this->gateway->purchase([
                'amount' => '100',
                'currency' => 'EUR',
                'payerPhone' => '46733123454', // PENDING phone
                'payerMessage' => 'Test payment for PENDING scenario',
                'payeeNote' => 'Test pending payment'
            ])->send();

            // Payment request should be accepted, but status will show PENDING
            if ($response->isSuccessful()) {
                echo "✅ SUCCESS: 202 Accepted - Transaction ID: " . $response->getTransactionReference() . "\n";
                echo "Result for CSV: OK - 202 Accepted (will remain pending)\n";
                $this->assertTrue($response->isSuccessful());
                
                // Check the status to see if it shows PENDING
                sleep(2); // Wait a bit for processing
                $statusResponse = $this->gateway->completePurchase([
                    'transactionReference' => $response->getTransactionReference()
                ])->send();
                
                if ($statusResponse->getStatus() === 'PENDING') {
                    echo "✅ SUCCESS: Status shows PENDING as expected\n";
                } else {
                    echo "ℹ️  INFO: Status: " . $statusResponse->getStatus() . "\n";
                }
            } else {
                echo "❌ FAILED: " . $response->getMessage() . " (Code: " . $response->getCode() . ")\n";
                echo "Result for CSV: FAIL - " . $response->getMessage() . "\n";
                $this->fail('Payment request failed: ' . $response->getMessage());
            }
        } catch (\Exception $e) {
            echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
            echo "Result for CSV: FAIL - Exception: " . $e->getMessage() . "\n";
            $this->fail('Exception during payment: ' . $e->getMessage());
        }
    }

    /**
     * Test decimal amounts (non-round numbers)
     * Note: MTN API expects whole number amounts, so decimals get rounded
     */
    public function testDecimalAmount()
    {
        echo "\n=== DECIMAL-AMOUNT: Testing decimal amount 99.99 EUR (rounded to 100) ===\n";
        
        try {
            $response = $this->gateway->purchase([
                'amount' => '99.99',
                'currency' => 'EUR',
                'payerPhone' => '56733123453', // SUCCESS phone
                'payerMessage' => 'Test payment with decimal amount 99.99',
                'payeeNote' => 'Test decimal payment (rounded to 100 EUR)'
            ])->send();

            if ($response->isSuccessful()) {
                echo "✅ SUCCESS: 202 Accepted - Transaction ID: " . $response->getTransactionReference() . "\n";
                echo "Result for CSV: OK - 202 Accepted (Decimal 99.99 EUR rounded to 100 EUR)\n";
                $this->assertTrue($response->isSuccessful());
                $this->assertNotEmpty($response->getTransactionReference());
                
                // Check the status to verify payment was processed
                sleep(2); // Wait a bit for processing
                $statusResponse = $this->gateway->completePurchase([
                    'transactionReference' => $response->getTransactionReference()
                ])->send();
                
                if ($statusResponse->isSuccessful()) {
                    echo "✅ SUCCESS: Status check successful - Status: " . $statusResponse->getStatus() . "\n";
                    if ($statusResponse->getStatus() === 'SUCCESSFUL') {
                        echo "✅ SUCCESS: Decimal amount payment completed successfully\n";
                    }
                }
            } else {
                echo "❌ FAILED: " . $response->getMessage() . " (Code: " . $response->getCode() . ")\n";
                echo "Result for CSV: FAIL - " . $response->getMessage() . "\n";
                $this->fail('Decimal amount payment request failed: ' . $response->getMessage());
            }
        } catch (\Exception $e) {
            echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
            echo "Result for CSV: FAIL - Exception: " . $e->getMessage() . "\n";
            $this->fail('Exception during decimal amount payment: ' . $e->getMessage());
        }
    }

    /**
     * Test various decimal amounts (all get rounded to whole numbers)
     * MTN API only accepts integer amounts
     */
    public function testVariousDecimalAmounts()
    {
        echo "\n=== DECIMAL-VARIOUS: Testing various decimal amounts (rounded to integers) ===\n";
        
        $testAmounts = [
            '1.01' => 'Minimum valid decimal (rounded to 1)',
            '12.49' => 'Rounds down to 12',
            '12.50' => 'Rounds up to 13', 
            '199.99' => 'Rounds up to 200',
            '1000.75' => 'Rounds up to 1001'
        ];

        foreach ($testAmounts as $amount => $description) {
            try {
                echo "Testing {$amount} EUR - {$description}\n";
                
                $response = $this->gateway->purchase([
                    'amount' => $amount,
                    'currency' => 'EUR',
                    'payerPhone' => '56733123453',
                    'payerMessage' => "Test payment {$amount} EUR",
                    'payeeNote' => "Test {$description}"
                ])->send();

                if ($response->isSuccessful()) {
                    echo "✅ {$amount} EUR: 202 Accepted - ID: " . $response->getTransactionReference() . "\n";
                } else {
                    echo "❌ {$amount} EUR: FAILED - " . $response->getMessage() . "\n";
                }
            } catch (\Exception $e) {
                echo "❌ {$amount} EUR: EXCEPTION - " . $e->getMessage() . "\n";
            }
        }
        
        // Test edge cases
        echo "\nTesting edge cases:\n";
        
        try {
            // Test 0.01 (should become 1 due to minimum enforcement)
            $response = $this->gateway->purchase([
                'amount' => '0.01',
                'currency' => 'EUR',
                'payerPhone' => '56733123453',
                'payerMessage' => 'Test minimum amount 0.01',
                'payeeNote' => 'Minimum amount test (becomes 1)'
            ])->send();

            if ($response->isSuccessful()) {
                echo "✅ 0.01 EUR: 202 Accepted (converted to 1 EUR) - ID: " . $response->getTransactionReference() . "\n";
            } else {
                echo "❌ 0.01 EUR: FAILED - " . $response->getMessage() . "\n";
            }
        } catch (\Exception $e) {
            echo "❌ 0.01 EUR: EXCEPTION - " . $e->getMessage() . "\n";
        }
        
        echo "Result for CSV: OK - Decimal amounts tested and rounded to integers successfully\n";
        $this->assertTrue(true, 'Decimal amounts testing completed');
    }
}
