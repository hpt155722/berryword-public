<?php
// Start the session
session_start();

// Define the API URL for fetching a 5-letter word
$apiUrl = 'https://random-word-api.herokuapp.com/word?number=1&length=5';

// Fetch the word data from the API
$response = file_get_contents($apiUrl);

// Check if the API returned valid data
if ($response === FALSE) {
    die('Error occurred while fetching data from API.');
}

$data = json_decode($response, true);

// Check if the response contains valid data
if (empty($data)) {
    die('No word found in the response.');
}

// Get the 5-letter word from the response
$word = $data[0];

// Store the word in the session
$_SESSION['real_word'] = strtoupper($word);

// Comment out returning the word
// echo json_encode(['word' => $word]);
?>
