# Omnipay: MTN Mobile Money

**MTN Mobile Money driver for the Omnipay PHP payment processing library**


[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment processing library for PHP. This package implements MTN Mobile Money support for Omnipay.

## Table of Contents

- [Installation](#installation)
- [Basic Usage](#basic-usage)
- [Initialization](#initialization)
- [API Features](#api-features)
- [Sandbox Testing](#sandbox-testing)
- [Authentication Flow](#authentication-flow)
- [Payment Processing](#payment-processing)
- [Account Services](#account-services)
- [Error Handling](#error-handling)
- [Testing](#testing)
- [Contributing](#contributing)
- [Security](#security)
- [License](#license)

## Installation

Install via Composer:

```bash
composer require microweber-packages/omnipay-momo-mtn
```

## Basic Usage

```php
use Omnipay\Omnipay;

// Create gateway instance
$gateway = Omnipay::create('MoMoMtn');

// Initialize with credentials
$gateway->initialize([
    'apiUserId' => 'your-api-user-id',
    'apiKey' => 'your-api-key',
    'subscriptionKey' => 'your-subscription-key',
    'targetEnvironment' => 'sandbox', // or 'production'
]);

// Process a payment
$response = $gateway->purchase([
    'amount' => '100.00',
    'currency' => 'EUR',
    'payerPhone' => '56733123453',
    'payerMessage' => 'Payment for order #12345',
    'payeeNote' => 'Order payment received'
])->send();

if ($response->isSuccessful()) {
    echo "Payment successful! Transaction ID: " . $response->getTransactionReference();
} else {
    echo "Payment failed: " . $response->getMessage();
}
```

## Initialization

### Required Parameters

| Parameter | Description | Environment |
|-----------|-------------|-------------|
| `apiUserId` | API User ID (UUID) | Both |
| `apiKey` | API Key (UUID) | Both |
| `subscriptionKey` | Subscription Key | Both |
| `targetEnvironment` | Environment (`sandbox` or `production`) | Both |

### Optional Parameters

| Parameter | Description | Default |
|-----------|-------------|---------|
| `testMode` | Enable test mode | `true` |

### Example Initialization

```php
$gateway->initialize([
    'apiUserId' => '2bf15487-2309-46e8-82e9-1f658cf3a82c',
    'apiKey' => '7f00bd3a51d7485cbbd85d083e70481b',
    'subscriptionKey' => 'b95263cce7184eaba10d1d309ded4d59',
    'targetEnvironment' => 'sandbox',
    'testMode' => true
]);
```

## API Features

### ✅ Implemented Features

- **Authentication**
  - API User provisioning (sandbox only)
  - API Key generation (sandbox only)
  - OAuth 2.0 token generation
  
- **Payment Processing**
  - RequestToPay (payment initiation)
  - Payment status checking
  - Transaction reference tracking
  
- **Account Services**
  - Balance checking (limited in sandbox)
  - Account active status (limited in sandbox)

### 🔄 Payment Flow

1. **Initiate Payment** - Use `purchase()` method
2. **Get Transaction Reference** - Store the returned reference
3. **Check Status** - Use `completePurchase()` with the reference
4. **Handle Result** - Process based on status (SUCCESSFUL, FAILED, PENDING, etc.)

## Sandbox Testing

### Test Phone Numbers

| Phone Number | Expected Result |
|--------------|----------------|
| `56733123453` | SUCCESSFUL |
| `46733123450` | FAILED |
| `46733123451` | REJECTED |
| `46733123452` | TIMEOUT |
| `46733123454` | PENDING |

### Sandbox Credentials Setup

1. **Register at MTN Developer Portal**
   - Visit [MTN MoMo Developer Portal](https://momodeveloper.mtn.com)
   - Create account and subscribe to Collections product
   - Get your subscription key

2. **Generate Sandbox Credentials**
   ```php
   // This is automatically handled by the gateway in sandbox mode
   $gateway->createApiUser()->send(); // Creates API User
   $gateway->createApiKey()->send();  // Creates API Key
   ```

## Authentication Flow

### OAuth 2.0 Token Generation

```php
// Tokens are automatically managed by the gateway
// Manual token generation (if needed):
$tokenResponse = $gateway->createToken()->send();
if ($tokenResponse->isSuccessful()) {
    $accessToken = $tokenResponse->getAccessToken();
    $expiresIn = $tokenResponse->getExpiresIn(); // seconds
}
```

## Payment Processing

### 1. Initiate Payment

```php
$response = $gateway->purchase([
    'amount' => '50.00',
    'currency' => 'EUR', // or 'UGX', 'GHS', etc.
    'payerPhone' => '56733123453',
    'payerMessage' => 'Payment for premium subscription',
    'payeeNote' => 'Monthly subscription fee',
    'externalId' => 'unique-external-reference' // optional
])->send();

if ($response->isSuccessful()) {
    $transactionId = $response->getTransactionReference();
    // Store transaction ID for status checking
} else {
    echo "Error: " . $response->getMessage();
}
```

### Decimal Amount Handling

> **Important**: MTN Mobile Money API only accepts whole number amounts (integers). Decimal amounts are automatically rounded to the nearest whole number:

```php
// These decimal amounts get rounded:
'99.49' → 99   // Rounds down
'99.50' → 100  // Rounds up  
'99.99' → 100  // Rounds up
'0.01'  → 1    // Minimum amount enforced

$response = $gateway->purchase([
    'amount' => '99.99',  // Will be processed as 100.00
    'currency' => 'EUR',
    'payerPhone' => '56733123453',
    // ... other parameters
])->send();
```

### 2. Check Payment Status

```php
$statusResponse = $gateway->completePurchase([
    'transactionReference' => $transactionId
])->send();

if ($statusResponse->isSuccessful()) {
    $status = $statusResponse->getStatus(); // SUCCESSFUL, FAILED, PENDING, etc.
    
    switch ($status) {
        case 'SUCCESSFUL':
            echo "Payment completed successfully!";
            break;
        case 'PENDING':
            echo "Payment pending user approval";
            break;
        case 'FAILED':
        case 'REJECTED':
            echo "Payment failed: " . $statusResponse->getReason();
            break;
    }
}
```

### Payment Statuses

| Status | Description |
|--------|-------------|
| `SUCCESSFUL` | Payment completed successfully |
| `PENDING` | Waiting for user approval |
| `FAILED` | Payment failed |
| `REJECTED` | User rejected the payment |
| `TIMEOUT` | Payment request timed out |

## Account Services

### Check Account Balance

```php
$response = $gateway->checkBalance([
    'accountHolderId' => '56733123453',
    'accountHolderType' => 'MSISDN' // Phone number format
])->send();

if ($response->isSuccessful()) {
    echo "Available Balance: " . $response->getAvailableBalance();
    echo "Currency: " . $response->getCurrency();
}
```

### Check Account Active Status

```php
$response = $gateway->checkAccountActive([
    'accountHolderId' => '56733123453',
    'accountHolderType' => 'MSISDN'
])->send();

if ($response->isSuccessful()) {
    $isActive = $response->isAccountActive();
    echo $isActive ? "Account is active" : "Account is not active";
}
```

> **Note**: Account services have limited availability in sandbox environment.

## Error Handling

### Common Error Codes

| HTTP Code | Description | Action |
|-----------|-------------|---------|
| `400` | Bad Request | Check request parameters |
| `401` | Unauthorized | Verify credentials |
| `404` | Not Found | Check transaction reference or account |
| `409` | Conflict | Duplicate transaction reference |
| `500` | Server Error | Retry later or contact support |

### Error Response Example

```php
if (!$response->isSuccessful()) {
    $errorCode = $response->getCode();
    $errorMessage = $response->getMessage();
    
    switch ($errorCode) {
        case 400:
            // Handle bad request
            break;
        case 401:
            // Handle authentication error
            break;
        case 404:
            // Handle not found
            break;
        default:
            // Handle other errors
    }
}
```

## Testing

### Run Tests

```bash
# Install dependencies
composer install

# Run all tests
composer test

# Run specific test suite
vendor/bin/phpunit tests/PaymentTest.php

# Run with detailed output
vendor/bin/phpunit --testdox
```

### Test Coverage

- **Authentication Tests**: OAuth token generation and validation
- **Payment Tests**: RequestToPay and status checking  
- **Account Tests**: Balance and active status checking
- **Integration Tests**: Full payment workflows
- **Validation Tests**: Parameter validation and error handling

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass (`composer test`)
6. Commit your changes (`git commit -am 'Add amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Create a Pull Request

### Development Setup

```bash
# Clone repository
git clone https://github.com/microweber-packages/omnipay-momo-mtn.git

# Install dependencies
composer install

# Run tests to ensure everything works
composer test
```

## Security

### Credential Management

- **Never commit credentials** to version control
- Use environment variables for sensitive data
- Rotate API keys regularly
- Use different credentials for sandbox and production

### Environment Variables

```bash
# .env file example
MTN_MOMO_API_USER_ID=your-api-user-id
MTN_MOMO_API_KEY=your-api-key
MTN_MOMO_SUBSCRIPTION_KEY=your-subscription-key
MTN_MOMO_ENVIRONMENT=sandbox
```

```php
// Using environment variables
$gateway->initialize([
    'apiUserId' => $_ENV['MTN_MOMO_API_USER_ID'],
    'apiKey' => $_ENV['MTN_MOMO_API_KEY'],
    'subscriptionKey' => $_ENV['MTN_MOMO_SUBSCRIPTION_KEY'],
    'targetEnvironment' => $_ENV['MTN_MOMO_ENVIRONMENT'] ?? 'sandbox',
]);
```

## API Documentation

For detailed API documentation, visit:
- [MTN MoMo API Documentation](https://momodeveloper.mtn.com/api-documentation/)
- [Omnipay Documentation](https://omnipay.thephpleague.com/)

## Requirements

- PHP 8.0 or higher
- Omnipay Common 3.4+
- cURL extension
- JSON extension

## Changelog

### v1.0.0 (2024-XX-XX)
- Initial release
- Complete authentication flow
- Payment processing (RequestToPay)
- Payment status checking
- Account balance and status checking
- Comprehensive test suite
- Full sandbox integration

## Support

- **Issues**: [GitHub Issues](https://github.com/microweber-packages/omnipay-momo-mtn/issues)
- **Documentation**: [MTN MoMo Developer Portal](https://momodeveloper.mtn.com/)
- **Email**: peter@microweber.com

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Acknowledgments

- [Omnipay](https://github.com/thephpleague/omnipay) - The PHP payment processing framework
- [MTN Mobile Money](https://www.mtn.com/momo/) - Mobile Money service provider
- [Microweber](https://microweber.com/) - Project sponsors

---

**Made with ❤️ by [Microweber](https://microweber.com/)**