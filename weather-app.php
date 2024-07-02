<?php
// Enable error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set CORS headers to allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Retrieve and sanitize the visitor's name
    $visitor_name = isset($_GET['visitor_name']) ? htmlspecialchars($_GET['visitor_name']) : null;

    if (empty($visitor_name)) {
        echo json_encode(["error" => "Please enter a valid name. Encode your request in the form: 'http://kendrick.infinityfreeapp.com/weather-app.php?visitor_name=YourName'"]);
        exit;
    }

    // Get the client's IP address
    $client_ip = $_SERVER['REMOTE_ADDR'];

    // Initialize city and temperature variables
    $city = 'Unknown';
    $temperature = 'Unknown';

    // Define API keys and endpoints
    $weather_api_keys = [
        'weatherapi' => '6c71540d41ec4442a8c51559240107',
        'openweathermap' => 'c62a80c3b0148cfab2eef4a80363c853'
    ];
    $location_token = '168b0b17f6478f';
    $location_url = "http://ipinfo.io/{$client_ip}?token={$location_token}";

    try {
        // Fetch the location data from ipinfo.io
        $location_response = @file_get_contents($location_url);

        if ($location_response === FALSE) {
            throw new Exception('Failed to get location data.');
        }

        // Decode the JSON response from ipinfo.io
        $location_data = json_decode($location_response, true);

        // Extract the city from the location data
        $city = $location_data['city'] ?? 'Unknown';

        // Define the weather API URLs
        $weather_urls = [
            "weatherapi" => "http://api.weatherapi.com/v1/current.json?key={$weather_api_keys['weatherapi']}&q={$city}&aqi=no",
            "openweathermap" => "http://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$weather_api_keys['openweathermap']}&units=metric",
            "openmeteo" => "https://api.open-meteo.com/v1/forecast?latitude={$location_data['loc']}&longitude={$location_data['loc']}&hourly=temperature_2m"
        ];

        // Attempt to fetch the weather data from each API in order
        $weather_data = null;
        foreach ($weather_urls as $key => $url) {
            $weather_response = @file_get_contents($url);
            if ($weather_response !== FALSE) {
                $weather_data = json_decode($weather_response, true);
                if ($key == 'weatherapi') {
                    $temperature = $weather_data['current']['temp_c'] ?? 'Unknown';
                } elseif ($key == 'openweathermap') {
                    $temperature = $weather_data['main']['temp'] ?? 'Unknown';
                } elseif ($key == 'openmeteo') {
                    $temperature = $weather_data['hourly']['temperature_2m'][0] ?? 'Unknown';
                }
                break;
            }
        }

        if ($weather_data === null) {
            throw new Exception('Failed to get weather data from all sources.');
        }

        // Prepare the response
        $response = [
            "client_ip" => $client_ip,
            "location" => $city,
            "greeting" => "Hello, " . htmlspecialchars($visitor_name) . "! The temperature is " . $temperature . " Celsius in " . $city
        ];

        // Send the response as JSON
        header('Content-Type: application/json');
        echo json_encode($response);

    } catch (Exception $e) {
        // Handle exceptions and send an error response
        header('Content-Type: application/json');
        echo json_encode(["error" => htmlspecialchars($e->getMessage())]);
    }
} else {
    // Send a 405 Method Not Allowed response if the request method is not GET
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
}
?>
