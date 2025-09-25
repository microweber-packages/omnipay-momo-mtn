<?php
/**
 * MTN Mobile Money Callback URL Testing with webhook.site
 * 
 * This example shows how to use webhook.site to test MTN MoMo callbacks.
 * webhook.site provides a temporary URL that displays all incoming HTTP requests.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Omnipay\Omnipay;

// Create gateway instance
$gateway = Omnipay::create('MoMoMtn');

// Initialize with your credentials
$gateway->initialize([
    'apiUserId' => '2bf15487-2309-46e8-82e9-1f658cf3a82c',
    'apiKey' => '7f00bd3a51d7485cbbd85d083e70481b',
    'subscriptionKey' => 'b95263cce7184eaba10d1d309ded4d59',
    'targetEnvironment' => 'sandbox',
    'testMode' => true
]);

// Generate a unique webhook.site URL for testing
$uniqueId = 'momo-test-' . uniqid();
$webhookUrl = "https://webhook.site/{$uniqueId}";

echo "=== MTN MoMo Callback Testing Setup ===\n";
echo "Generated webhook URL: {$webhookUrl}\n";
echo "You can visit this URL in your browser to monitor callbacks\n\n";

try {
    // Initiate payment with callback URL
    $response = $gateway->purchase([
        'amount' => '25.00',
        'currency' => 'EUR',
        'payerPhone' => '56733123453', // SUCCESS phone number
        'payerMessage' => 'Testing callback with webhook.site',
        'payeeNote' => 'Callback test payment',
        'callbackUrl' => $webhookUrl,
        'externalId' => $uniqueId
    ])->send();

    if ($response->isSuccessful()) {
        $transactionId = $response->getTransactionReference();
        echo "✅ Payment initiated successfully!\n";
        echo "Transaction ID: {$transactionId}\n";
        echo "Callback URL: {$webhookUrl}\n\n";
        
        echo "=== TESTING INSTRUCTIONS ===\n";
        echo "1. Open your browser and visit: {$webhookUrl}\n";
        echo "2. Wait for MTN to send callback requests (may take 1-5 minutes)\n";
        echo "3. Refresh the webhook.site page to see incoming requests\n";
        echo "4. Look for POST requests containing transaction status updates\n";
        echo "5. The callback data will show the payment status (SUCCESSFUL, PENDING, etc.)\n\n";
        
        echo "=== EXPECTED CALLBACK DATA ===\n";
        echo "MTN will send a JSON payload similar to:\n";
        echo "{\n";
        echo "  \"referenceId\": \"{$transactionId}\",\n";
        echo "  \"status\": \"SUCCESSFUL\",\n";
        echo "  \"amount\": \"25.00\",\n";
        echo "  \"currency\": \"EUR\",\n";
        echo "  \"financialTransactionId\": \"...\",\n";
        echo "  \"externalId\": \"{$uniqueId}\"\n";
        echo "}\n\n";
        
        echo "💡 TIP: Keep the webhook.site tab open to see real-time callback data!\n";
        
    } else {
        echo "❌ Payment failed: " . $response->getMessage() . "\n";
        echo "Code: " . $response->getCode() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n=== CALLBACK TESTING COMPLETE ===\n";