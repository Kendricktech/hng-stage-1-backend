<?php
// Enable error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set CORS headers to allow cross-origin requests
header("Access-Control-Allow-Origin: *<?php
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
        echo json_encode(["error" => "Please enter a valid name. Encode your request in the form: http://kendrick.infinityfreeapp.com/weather-app.php?visitor_name=YourName"]);
        exit;
    }

    // Get the client's IP address
    $client_ip = $_SERVER['REMOTE_ADDR'];

    // Initialize city and temperature variables
    $city = 'Unknown';
    $temperature = 'Unknown';

    // Define API keys and endpoints
    $weather_api_key = '6c71540d41ec4442a8c51559240107';
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

        // Fetch the weather data from weatherapi.com
        $weather_url = "http://api.weatherapi.com/v1/current.json?key={$weather_api_key}&q={$city}&aqi=no";
        $weather_response = @file_get_contents($weather_url);

        if ($weather_response === FALSE) {
            throw new Exception('Failed to get weather data.');
        }

        // Decode the JSON response from weatherapi.com
        $weather_data = json_decode($weather_response, true);

        // Extract temperature from the weather data
        $temperature = $weather_data['current']['temp_c'] ?? 'Unknown';

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
");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Retrieve and sanitize the visitor's name
    $visitor_name = isset($_GET['visitor_name']) ? htmlspecialchars($_GET['visitor_name']) : 'Unknown Visitor';

    // Get the client's IP address
    $client_ip = $_SERVER['REMOTE_ADDR'];

    // Initialize city and temperature variables
    $city = 'Unknown';
    $temperature = 'Unknown';

    // Define API keys and endpoints
    $weather_api_key = '6c71540d41ec4442a8c51559240107';
    $location_token = '168b0b17f6478f';
    $location_url = "http://ipinfo.io/{$client_ip}?token={$location_token}";

    try {
        // Fetch the location data from ipinfo.io
        $location_response = @file_get_contents($location_url);

        if ($location_response === FALSE) {
            throw new Exception('Failed to get location data.');
        }

       
        $location_data = json_decode($location_response, true);

        
        $city = $location_data['city'] ?? 'Unknown';

       
        $weather_url = "http://api.weatherapi.com/v1/current.json?key={$weather_api_key}&q={$city}&aqi=no";
        $weather_response = @file_get_contents($weather_url);

        if ($weather_response === FALSE) {
            throw new Exception('Failed to get weather data.');
        }

      
        $weather_data = json_decode($weather_response, true);

        
        $temperature = $weather_data['current']['temp_c'] ?? 'Unknown';

     
        $response = [
            "client_ip" => $client_ip,
            "location" => $city,
            "greeting" => "Hello, " . htmlspecialchars($visitor_name) . "! The temperature is " . $temperature . " Celsius in " . $city
        ];

     
        header('Content-Type: application/json');
        echo json_encode($response);

    } catch (Exception $e) {
      
        header('Content-Type: application/json');
        echo json_encode(["error" => htmlspecialchars($e->getMessage())]);
    }
} else {
    
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
}
?>
