<?php
// Get the word from the request (GET or POST)
$word = isset($_GET['word']) ? $_GET['word'] : '';

// Your Merriam-Webster API key
$apiKey = 'OMIT';

// URL for the Merriam-Webster API
$url = "https://www.dictionaryapi.com/api/v3/references/sd4/json/{$word}?key={$apiKey}";

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the cURL session and get the response
$response = curl_exec($ch);

// Check if the request was successful
if ($response === false) {
    echo json_encode(['error' => 'Error with the API request.']);
    exit;
}

// Decode the JSON response
$data = json_decode($response, true);

// Check if the word is found in the dictionary
if (isset($data[0]['meta']['id'])) {
    // Word is found, it is real
    echo json_encode(['is_real' => true, 'word' => $word]);
} else {
    // Word is not found, it is not real
    echo json_encode(['is_real' => false, 'word' => $word, 'error' => 'Word not found']);
}

// Close the cURL session
curl_close($ch);
?>
