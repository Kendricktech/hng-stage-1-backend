<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  
    $visitor_name = isset($_GET['visitor_name']) ? htmlspecialchars($_GET['visitor_name']) : 'Unknown Visitor';
  
    $client_ip = $_SERVER['REMOTE_ADDR'];

    
    $city = 'Unknown';
    $temperature = 'Unknown';

    
    $api_key = '6c71540d41ec4442a8c51559240107';

    try {
       
        $weather_url = "http://api.weatherapi.com/v1/current.json?key={$api_key}&q={$client_ip}&aqi=no";
      
        $weather_response = @file_get_contents($weather_url);

        
        if ($weather_response === FALSE) {
            throw new Exception('Failed to get weather data.');
        }

        
        $weather_data = json_decode($weather_response, true);

       
        $city = $weather_data['location']['name'] ?? 'Unknown';
        $temperature = $weather_data['current']['temp_c'] ?? 'Unknown';

        
        $response = [
            "client_ip" => $client_ip,
            "location" => $city,
            "greeting" => "Hello, " . htmlspecialchars($visitor_name) . "! The temperature is " . $temperature . " Celsius in " . $city
        ];

        echo json_encode($response);

    } catch (Exception $e) {
        
        echo json_encode(["error" => htmlspecialchars($e->getMessage())]);
        ECHO"MY SERVER IS WWORKING";
    }
}
?>
