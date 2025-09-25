<?php
/**
 * Production-ready MTN Mobile Money Callback Handler
 * 
 * This is an example of how to implement a secure, production-ready
 * callback handler for MTN Mobile Money payments.
 */

// Security headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust as needed for your setup

// 1. Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

// 2. Verify HTTPS in production
if (!isset($_SERVER['HTTPS']) && getenv('ENVIRONMENT') === 'production') {
    http_response_code(403);
    exit(json_encode(['error' => 'HTTPS required in production']));
}

// 3. Get request data
$rawData = file_get_contents('php://input');
$headers = getallheaders();

// 4. Log incoming request for debugging
error_log('MTN MoMo Callback - Raw Data: ' . $rawData);
error_log('MTN MoMo Callback - Headers: ' . json_encode($headers));

// 5. Validate and parse JSON
if (empty($rawData)) {
    error_log('MTN MoMo Callback - Error: Empty payload');
    http_response_code(400);
    exit(json_encode(['error' => 'Empty payload']));
}

$payload = json_decode($rawData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('MTN MoMo Callback - Error: Invalid JSON - ' . json_last_error_msg());
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid JSON']));
}

// 6. Validate required fields
$requiredFields = ['referenceId', 'status'];
foreach ($requiredFields as $field) {
    if (!isset($payload[$field]) || empty($payload[$field])) {
        error_log("MTN MoMo Callback - Error: Missing required field: {$field}");
        http_response_code(400);
        exit(json_encode(['error' => "Missing required field: {$field}"]));
    }
}

try {
    // 7. Extract payment data
    $referenceId = $payload['referenceId'];
    $status = $payload['status'];
    $amount = $payload['amount'] ?? null;
    $currency = $payload['currency'] ?? null;
    $financialTransactionId = $payload['financialTransactionId'] ?? null;
    $externalId = $payload['externalId'] ?? null;
    $reason = $payload['reason'] ?? null;

    // 8. Log processing start
    error_log("MTN MoMo Callback - Processing: {$referenceId} -> {$status}");

    // 9. Check for duplicate processing (idempotency)
    if (isCallbackAlreadyProcessed($referenceId, $status)) {
        error_log("MTN MoMo Callback - Already processed: {$referenceId}");
        http_response_code(200);
        exit(json_encode(['status' => 'already_processed']));
    }

    // 10. Process payment based on status
    $result = processPaymentCallback($payload);

    if ($result['success']) {
        // 11. Mark as processed
        markCallbackAsProcessed($referenceId, $status);
        
        // 12. Log successful processing
        error_log("MTN MoMo Callback - Success: {$referenceId} processed successfully");
        
        // 13. Return success response
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Callback processed successfully']);
        
    } else {
        // 14. Handle processing error
        error_log("MTN MoMo Callback - Processing Error: {$referenceId} - " . $result['error']);
        
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Processing failed']);
    }

} catch (Exception $e) {
    // 15. Handle exceptions
    error_log('MTN MoMo Callback - Exception: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
}

/**
 * Check if callback has already been processed (idempotency)
 */
function isCallbackAlreadyProcessed($referenceId, $status) {
    // Example: Check database for existing callback record
    // return checkDatabase("SELECT COUNT(*) FROM callback_log WHERE reference_id = ? AND status = ?", [$referenceId, $status]) > 0;
    
    // For demo purposes, always return false
    return false;
}

/**
 * Mark callback as processed
 */
function markCallbackAsProcessed($referenceId, $status) {
    // Example: Insert into callback log
    // insertDatabase("INSERT INTO callback_log (reference_id, status, processed_at) VALUES (?, ?, NOW())", [$referenceId, $status]);
}

/**
 * Process the payment callback
 */
function processPaymentCallback($payload) {
    $referenceId = $payload['referenceId'];
    $status = $payload['status'];
    $amount = $payload['amount'] ?? null;
    $reason = $payload['reason'] ?? null;

    try {
        switch ($status) {
            case 'SUCCESSFUL':
                return processSuccessfulPayment($payload);
                
            case 'FAILED':
            case 'REJECTED':
                return processFailedPayment($payload);
                
            case 'PENDING':
                return processPendingPayment($payload);
                
            case 'TIMEOUT':
                return processTimeoutPayment($payload);
                
            default:
                error_log("MTN MoMo Callback - Unknown status: {$status}");
                return ['success' => false, 'error' => "Unknown status: {$status}"];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Process successful payment
 */
function processSuccessfulPayment($payload) {
    $referenceId = $payload['referenceId'];
    $amount = $payload['amount'];
    $financialTransactionId = $payload['financialTransactionId'] ?? null;
    
    // 1. Update payment status in database
    // updatePaymentStatus($referenceId, 'completed', $financialTransactionId);
    
    // 2. Send confirmation email to customer
    // sendPaymentConfirmationEmail($referenceId);
    
    // 3. Trigger any post-payment actions (e.g., activate service, send receipt)
    // triggerPostPaymentActions($referenceId);
    
    // 4. Update order status if applicable
    // updateOrderStatus($referenceId, 'paid');
    
    error_log("MTN MoMo Callback - Payment completed successfully: {$referenceId}");
    return ['success' => true, 'action' => 'payment_completed'];
}

/**
 * Process failed payment
 */
function processFailedPayment($payload) {
    $referenceId = $payload['referenceId'];
    $reason = $payload['reason'] ?? 'Payment failed';
    
    // 1. Update payment status in database
    // updatePaymentStatus($referenceId, 'failed', null, $reason);
    
    // 2. Send failure notification to customer
    // sendPaymentFailureNotification($referenceId, $reason);
    
    // 3. Handle failed payment (e.g., restore cart, cancel order)
    // handleFailedPayment($referenceId);
    
    error_log("MTN MoMo Callback - Payment failed: {$referenceId} - {$reason}");
    return ['success' => true, 'action' => 'payment_failed'];
}

/**
 * Process pending payment
 */
function processPendingPayment($payload) {
    $referenceId = $payload['referenceId'];
    
    // 1. Update status to pending
    // updatePaymentStatus($referenceId, 'pending');
    
    // 2. Set up timeout handling if needed
    // schedulePaymentTimeout($referenceId);
    
    error_log("MTN MoMo Callback - Payment pending: {$referenceId}");
    return ['success' => true, 'action' => 'payment_pending'];
}

/**
 * Process timeout payment
 */
function processTimeoutPayment($payload) {
    $referenceId = $payload['referenceId'];
    
    // 1. Update status to timeout
    // updatePaymentStatus($referenceId, 'timeout');
    
    // 2. Handle timeout (similar to failed payment)
    // handleTimeoutPayment($referenceId);
    
    error_log("MTN MoMo Callback - Payment timeout: {$referenceId}");
    return ['success' => true, 'action' => 'payment_timeout'];
}

/**
 * Additional helper functions would go here:
 * 
 * - Database connection and query functions
 * - Email sending functions
 * - SMS notification functions
 * - Order management functions
 * - Logging and monitoring functions
 * - Security verification functions
 */

?>