<?php
// Enable error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set CORS headers to allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json"); // Ensure the response is in JSON format


if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Retrieve and sanitize the visitor's name
    $visitor_name = isset($_GET['visitor_name']) ? htmlspecialchars($_GET['visitor_name']) : 'Unknown Visitor';

    $client_ip = $_SERVER['REMOTE_ADDR'];

    $city = 'Unknown';
    $temperature = 'Unknown';

    $api_key = '6c71540d41ec4442a8c51559240107';

    try {
       
        $weather_url = "http://api.weatherapi.com/v1/current.json?key={$api_key}&q={$client_ip}&aqi=no";
        
        // Fetch the weather data from the API
        $weather_response = file_get_contents($weather_url);

        // Check if the response is empty or not
        if ($weather_response === FALSE) {
            http_response_code(500);
            throw new Exception('Failed to get weather data.');
        }

        // Decode the JSON response from the weather API
        $weather_data = json_decode($weather_response, true);

        // Check if JSON decoding was successful
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(500);
            throw new Exception('Error parsing weather data.');
        }

        // Extract city and temperature from the weather data
        $city = $weather_data['location']['name'] ?? 'Unknown';
        $temperature = $weather_data['current']['temp_c'] ?? 'Unknown';

        // Prepare the response
        $response = [
            "client_ip" => $client_ip,
            "location" => $city,
            "greeting" => "Hello, " . htmlspecialchars($visitor_name) . "! The temperature is " . $temperature . " Celsius in " . $city
        ];

        // Send a 200 OK response with the JSON data
        http_response_code(200);
        echo json_encode($response);

    } catch (Exception $e) {
        // Handle exceptions and send a 400 Bad Request response with the error message
        http_response_code(400);
        $error_response = [
            "error" => htmlspecialchars($e->getMessage())
        ];
        echo json_encode($error_response);
    }
} else {
    // Send a 405 Method Not Allowed response if the request method is not GET
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
}
?>
