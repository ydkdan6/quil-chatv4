<?php 
session_start();

if (isset($_SESSION['unique_id'])) {
    include_once "config.php";
    $outgoing_id = $_SESSION['unique_id'];
    $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    if (!empty($message)) {
        // Check if the message starts with @ai
        if (strpos($message, '@ai') === 0) {
            // This is a prompt for the AI, extract the query part
            $aiQuery = substr($message, 4); // Extract the query after @ai

            // Send $aiQuery to Wit.ai or your AI service and obtain the AI response
            $aiResponse = getAiResponse($aiQuery); // Implement getAiResponse() to send the query and get a response

            // Insert the user's message into the database
            $sql = mysqli_query($conn, "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg)
                                        VALUES ({$incoming_id}, {$outgoing_id}, '{$message}')") or die();

            // Insert the AI's response into the database
            $sql = mysqli_query($conn, "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg)
                                        VALUES ({$incoming_id}, {$outgoing_id}, '{$aiResponse}')") or die();
        } else {
            // This is a regular chat message, insert it into the database
            $sql = mysqli_query($conn, "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg)
                                        VALUES ({$incoming_id}, {$outgoing_id}, '{$message}')") or die();
        }
    }
} else {
    header("location: ../login.php");
}

// Function to send query to Wit.ai and get a response
// Function to send a query to Wit.ai and get a response
function getAiResponse($query) {
    // Replace 'your-wit-ai-api-key' with your Wit.ai Server Access Token
    $witApiKey = 'ULLLDZATW7UPIWQ4MGBTYV4NJRRDSIGL';

    // Wit.ai API endpoint
    $witApiUrl = 'https://api.wit.ai/message?q=' . urlencode($query);

    // Set up options for the API request
    $options = [
        'http' => [
            'header' => "Authorization: Bearer $witApiKey",
            'method' => 'GET',
        ],
    ];

    // Create a context for the API request
    $context = stream_context_create($options);

    // Send the request to Wit.ai
    $result = file_get_contents($witApiUrl, false, $context);

    if ($result === false) {
        return "AI Response: An error occurred while querying the AI service.";
    }

    // Parse the response from Wit.ai
    $data = json_decode($result, true);

    // Extract the AI's response from the data
    $aiResponse = 'AI: ' . $data['_text'];

    return $aiResponse;
}

?>
