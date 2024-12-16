<?php
// Start the session
session_start();

// Define the API URL for fetching a 5-letter word
$apiUrl = 'https://random-word-api.herokuapp.com/word?number=1&length=5';

// Fetch the word data from the API
$response = @file_get_contents($apiUrl);

// Check if the API returned valid data
if ($response === FALSE) {
    // API call failed, fallback to fetching the word from a text file
    $word = getWordFromFile();
    if ($word === false) {
        die('Error: Unable to retrieve word from file or API.');
    }
} else {
    // Decode the API response
    $data = json_decode($response, true);

    // Check if the response contains valid data
    if (empty($data)) {
        // If no valid data, fallback to fetching from the file
        $word = getWordFromFile();
        if ($word === false) {
            die('Error: Unable to retrieve word from file or API.');
        }
    } else {
        // Get the 5-letter word from the API response
        $word = strtoupper($data[0]);
    }
}

// Store the word in the session
$_SESSION['real_word'] = $word;

// Helper function to retrieve word from a text file
function getWordFromFile() {
    $filePath = 'words.txt'; // Path to the text file containing words
    
    // Check if the file exists
    if (!file_exists($filePath)) {
        return false;
    }

    // Read the file content
    $words = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Check if the file is empty or invalid
    if (!$words || !is_array($words)) {
        return false;
    }

    // Filter the words to get only 5-letter words
    $fiveLetterWords = array_filter($words, function($word) {
        return strlen(trim($word)) === 5;
    });

    // If no 5-letter words are found, return false
    if (empty($fiveLetterWords)) {
        return false;
    }

    // Return a random 5-letter word
    return strtoupper($fiveLetterWords[array_rand($fiveLetterWords)]);
}

// Optionally: Uncomment to return the word (if you're not using session)
echo json_encode(['word' => $_SESSION['real_word']]);
?>
