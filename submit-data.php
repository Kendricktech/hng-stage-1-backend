<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Greeting Form</title>
</head>
<body>
    <h1>Enter Your Name</h1>
    <form id="greetingForm">
        <label for="visitor_name">Name:</label>
        <input type="text" id="visitor_name" name="visitor_name" required>
        <button type="submit">Submit</button>
        <input type="hidden" id="client_ip" name="client_ip">
    </form>
    <p id="responseOutput"></p>

    <script>
        // Fetch the client's IP address using an external service
        fetch('https://api.ipify.org?format=json')
            .then(response => response.json())
            .then(data => {
                document.getElementById('client_ip').value = data.ip;
            });

        // Handle form submission
        document.getElementById('greetingForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission

            const form = event.target;
            const visitorName = form.visitor_name.value;
            const clientIp = form.client_ip.value;

            // Construct the query parameters for the GET request
            const queryParams = new URLSearchParams({
                visitor_name: visitorName,
                client_ip: clientIp
            });

            // Send the GET request to the server
            fetch(`http://kendrick.infinityfreeapp.com/weather-app.php?${queryParams}`)
                .then(response => response.json())
                .then(data => {
                    // Check for errors in the response
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    // Display the response in the paragraph element
                    const responseOutput = document.getElementById('responseOutput');
                    responseOutput.innerHTML = `
                        <p>IP: ${data.client_ip}</p>
                        <p>Location: ${data.location}</p>
                        <p>Greeting: ${data.greeting}</p>
                    `;
                })
                .catch(error => {
                    // Display the error in the paragraph element
                    const responseOutput = document.getElementById('responseOutput');
                    responseOutput.textContent = `Error: ${error.message}`;
                });
        });
    </script>
</body>
</html>
