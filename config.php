<?php
// Database connection configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'elearning_platform';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Load environment variables (optional, recommended)
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Function to call OpenRouter API
function callOpenRouterAPI($messages) {
    $api_key = $_ENV['OPENROUTER_API_KEY'] ?? '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';// Replace with $_ENV['OPENROUTER_API_KEY'] after securing
    $url = 'https://openrouter.ai/api/v1/chat/completions';
    $data = [
        'model' => 'deepseek/deepseek-chat:free',
        'messages' => $messages
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_key,
        'HTTP-Referer: http://localhost/elearning_platform',
        'X-Title: E-Learning Platform',
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        throw new Exception("API request failed with status $http_code: $response");
    }

    return json_decode($response, true);
}
?>