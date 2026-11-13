<?php
// ============================================
// Assignment 10: HTTP Request Handler & REST API
// ============================================

// 1) Set the response header to return JSON
header("Content-Type: application/json; charset=UTF-8");

// 2) Read the incoming request method
$method = $_SERVER['REQUEST_METHOD'];

// 3) Read the headers to trace security headers such as Authorization
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;

// 4) Extract data from the request body and parse it
$rawInput = file_get_contents("php://input");
$data = [];

if (!empty($rawInput)) {
    $data = json_decode($rawInput, true);

    // Handle JSON parsing errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400); // Bad Request
        echo json_encode([
            "status"  => "error",
            "code"    => 400,
            "message" => "Invalid JSON format: " . json_last_error_msg()
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Helper function to output a structured response
function sendResponse($code, $payload) {
    http_response_code($code);
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// 5) Build a conditional structure to distinguish between request methods
switch ($method) {

    // ---------- GET ----------
    case 'GET':
        sendResponse(200, [
            "status"  => "success",
            "code"    => 200,
            "method"  => "GET",
            "message" => "GET request received successfully",
            "headers" => [
                "Authorization" => $authHeader ?? "Not provided"
            ],
            "data"    => $_GET // Query string data
        ]);
        break;

    // ---------- POST ----------
    case 'POST':
        if (empty($data)) {
            sendResponse(400, [
                "status"  => "error",
                "code"    => 400,
                "method"  => "POST",
                "message" => "No JSON body provided"
            ]);
        }
        sendResponse(201, [ // 201 Created
            "status"  => "success",
            "code"    => 201,
            "method"  => "POST",
            "message" => "Resource created successfully",
            "headers" => [
                "Authorization" => $authHeader ?? "Not provided"
            ],
            "received"=> $data
        ]);
        break;

    // ---------- PUT ----------
    case 'PUT':
        if (empty($data)) {
            sendResponse(400, [
                "status"  => "error",
                "code"    => 400,
                "method"  => "PUT",
                "message" => "No JSON body provided for update"
            ]);
        }
        sendResponse(200, [
            "status"  => "success",
            "code"    => 200,
            "method"  => "PUT",
            "message" => "Resource updated successfully",
            "headers" => [
                "Authorization" => $authHeader ?? "Not provided"
            ],
            "updated" => $data
        ]);
        break;

    // ---------- DELETE ----------
    case 'DELETE':
        sendResponse(200, [
            "status"  => "success",
            "code"    => 200,
            "method"  => "DELETE",
            "message" => "Resource deleted successfully",
            "headers" => [
                "Authorization" => $authHeader ?? "Not provided"
            ],
            "data"    => $data
        ]);
        break;

    // ---------- Unsupported method ----------
    default:
        header("Allow: GET, POST, PUT, DELETE"); // Header listing allowed methods
        sendResponse(405, [ // 405 Method Not Allowed
            "status"  => "error",
            "code"    => 405,
            "method"  => $method,
            "message" => "Method Not Allowed. Supported methods: GET, POST, PUT, DELETE"
        ]);
        break;
}