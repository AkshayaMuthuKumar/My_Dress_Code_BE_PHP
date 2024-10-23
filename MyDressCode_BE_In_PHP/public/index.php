<?php
require '../vendor/autoload.php'; // Adjust the path if needed

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require 'config/database.php'; // Adjust the path as needed

use Razorpay\Api\Api;

$razorpayKeyId = getenv('RAZORPAY_KEY_ID');
$razorpayKeySecret = getenv('RAZORPAY_KEY_SECRET');

// Enable CORS for frontend requests
header("Access-Control-Allow-Origin: http://localhost"); // Allow specific origin
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// Handle preflight requests for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit; // Stop further execution for OPTIONS request
}

// Create a new instance of Razorpay API
$razorpay = new Api($razorpayKeyId, $razorpayKeySecret);

// Set the uploads directory for serving static files
setupUploadsDirectory();

// Simple routing mechanism
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestUri) {
    case '/api/products':
        require 'routes/product.php'; // Include product routes
        break;

    case '/api/users':
        require 'routes/user.php'; // Include user routes
        break;

    case '/create-order':
        if ($requestMethod === 'POST') {
            createOrder($razorpay);
        } else {
            http_response_code(405);
            echo json_encode(['message' => 'Method not allowed']);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['message' => 'Not found']);
        break;
}

function setupUploadsDirectory() {
    $uploadsDir = __DIR__ . '/uploads';
    if (!file_exists($uploadsDir)) {
        mkdir($uploadsDir, 0777, true);
    }
}

function createOrder($razorpay) {
    // Get the JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    $amount = $input['amount'] ?? null;
    $currency = $input['currency'] ?? 'INR'; // Default currency

    if ($amount === null) {
        http_response_code(400);
        echo json_encode(['message' => 'Amount is required']);
        return;
    }

    // Create Razorpay order
    $options = [
        'amount' => $amount * 100, // Razorpay expects amount in smallest currency unit (like paise for INR)
        'currency' => $currency,
        'receipt' => 'receipt#1',
    ];

    try {
        $order = $razorpay->order->create($options);
        http_response_code(200);
        echo json_encode($order);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error creating order', 'error' => $e->getMessage()]);
    }
}
?>
