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
        echo json_encode(["error" => "Please enter a valid name. Encode your request in the form: 'http://yourdomain.com/api/hello?visitor_name=YourName'"]);
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
    $ipinfo_token = '168b0b17f6478f'; 
    $ipstack_token = 'IPSTACK_API_KEY'; 
    $ipgeolocation_token = 'ea90e7e8ff07435a9476aeac4dcf234e'; 
    $location_urls = [
        "ipinfo" => "https://ipinfo.io/{$client_ip}?token={$ipinfo_token}",
        "ipstack" => "http://api.ipstack.com/{$client_ip}?access_key={$ipstack_token}",
        "ipgeolocation" => "https://api.ipgeolocation.io/ipgeo?apiKey={$ipgeolocation_token}&ip={$client_ip}"
    ];

    try {
        $cities = [];
        $latitude = null;
        $longitude = null;

        // Fetch location data from each API
        foreach ($location_urls as $key => $url) {
            $location_response = @file_get_contents($url);
            if ($location_response !== FALSE) {
                $location_data = json_decode($location_response, true);
                switch ($key) {
                    case 'ipinfo':
                        $cities[] = $location_data['city'] ?? 'Unknown';
                        if (!isset($latitude) && isset($location_data['loc'])) {
                            list($latitude, $longitude) = explode(',', $location_data['loc']);
                        }
                        break;
                    case 'ipstack':
                        $cities[] = $location_data['city'] ?? 'Unknown';
                        if (!isset($latitude) && isset($location_data['latitude'])) {
                            $latitude = $location_data['latitude'];
                            $longitude = $location_data['longitude'];
                        }
                        break;
                    case 'ipgeolocation':
                        $cities[] = $location_data['city'] ?? 'Unknown';
                        if (!isset($latitude) && isset($location_data['latitude'])) {
                            $latitude = $location_data['latitude'];
                            $longitude = $location_data['longitude'];
                        }
                        break;
                }
            }
        }

        // Determine the most common city from the responses
        if (!empty($cities)) {
            $city_counts = array_count_values($cities);
            $max_count = max($city_counts);
            $most_common_cities = array_keys(array_filter($city_counts, function($count) use ($max_count) {
                return $count == $max_count;
            }));
            $city = $most_common_cities[array_rand($most_common_cities)];
        } else {
            $city = 'Unknown';
        }

        // Define the weather API URLs
        $weather_urls = [];
        if (isset($latitude) && isset($longitude)) {
            $weather_urls = [
                "weatherapi" => "http://api.weatherapi.com/v1/current.json?key={$weather_api_keys['weatherapi']}&q={$city}&aqi=no",
                "openweathermap" => "http://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$weather_api_keys['openweathermap']}&units=metric",
                "openmeteo" => "https://api.open-meteo.com/v1/forecast?latitude={$latitude}&longitude={$longitude}&hourly=temperature_2m"
            ];
        }

        // Attempt to fetch the weather data from each API in order
        $temperatures = [];
        foreach ($weather_urls as $key => $url) {
            $weather_response = @file_get_contents($url);
            if ($weather_response !== FALSE) {
                $weather_data = json_decode($weather_response, true);
                if ($key == 'weatherapi') {
                    $temp = $weather_data['current']['temp_c'] ?? null;
                    if ($temp !== null) $temperatures[] = strval($temp);
                } elseif ($key == 'openweathermap') {
                    $temp = $weather_data['main']['temp'] ?? null;
                    if ($temp !== null) $temperatures[] = strval($temp);
                } elseif ($key == 'openmeteo') {
                    $temp = $weather_data['hourly']['temperature_2m'][0] ?? null;
                    if ($temp !== null) $temperatures[] = strval($temp);
                }
            }
        }

        // Determine the most common temperature from the responses
        if (!empty($temperatures)) {
            $temperature_counts = array_count_values($temperatures);
            $max_count = max($temperature_counts);
            $most_common_temps = array_keys(array_filter($temperature_counts, function($count) use ($max_count) {
                return $count == $max_count;
            }));
            $temperature = $most_common_temps[array_rand($most_common_temps)];
        } else {
            $temperature = 'Unknown';
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
