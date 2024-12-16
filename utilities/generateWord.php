<?php
// Start the session
session_start();

// Merriam-Webster API key for normal mode
$apiKeyNormal = 'OMIT';

// Merriam-Webster API key for easy mode
$apiKeyEasy = 'OMIT';

// Define the API URL for fetching a 5-letter word
$apiUrl = 'https://random-word-api.herokuapp.com/word?number=1&length=5';

/**
 * Fetches a random 5-letter word and its definition based on the mode.
 *
 * @param string $mode The game mode (either 'easy' or 'normal')
 * @return array|false Returns an associative array with 'word' and 'definition', or false on failure.
 */
function getWordAndDefinition($mode) {
    global $apiUrl, $apiKeyNormal, $apiKeyEasy;
    
    // Step 1: Get a 5-letter word from the Random Word API
    $response = @file_get_contents($apiUrl);
    
    if ($response === FALSE) {
        return false;
    }
    
    $data = json_decode($response, true);
    
    if (empty($data) || !is_array($data)) {
        return false;
    }
    
    $word = strtoupper($data[0]); // Get the word and convert to uppercase
    
    // Choose the appropriate API key based on the mode
    if ($mode === 'easy') {
        $apiKey = $apiKeyEasy; // Easy mode API key
        $url = "https://www.dictionaryapi.com/api/v3/references/sd2/json/{$word}?key={$apiKey}";
    } else {
        $apiKey = $apiKeyNormal; // Normal mode API key
        $url = "https://www.dictionaryapi.com/api/v3/references/sd4/json/{$word}?key={$apiKey}";
    }
    
    // Step 2: Get the definition of the word from the Merriam-Webster API
    $response = @file_get_contents($url);
    
    if ($response === false) {
        return false;
    }
    
    $definitionData = json_decode($response, true);
    
    if (empty($definitionData) || !is_array($definitionData) || !isset($definitionData[0]['shortdef'])) {
        return false;
    }
    
    $definition = isset($definitionData[0]['shortdef']) && is_array($definitionData[0]['shortdef']) 
        ? implode('; ', $definitionData[0]['shortdef']) 
        : 'Definition not found for this word.';
    
    return [
        'word' => $word,
        'definition' => $definition
    ];
}

/**
 * Fallback method to retrieve a word from a file and its definition.
 *
 * @param string $mode The game mode (either 'easy' or 'normal')
 * @return array|false Returns an associative array with 'word' and 'definition', or false on failure.
 */
function getWordAndDefinitionFromFile($mode) {
    $filePath = 'words.txt'; // Path to the text file containing words
    
    if (!file_exists($filePath)) {
        return false;
    }
    
    $words = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    if (!$words || !is_array($words)) {
        return false;
    }
    
    $fiveLetterWords = array_filter($words, function($word) {
        return strlen(trim($word)) === 5;
    });
    
    if (empty($fiveLetterWords)) {
        return false;
    }
    
    $word = strtoupper($fiveLetterWords[array_rand($fiveLetterWords)]);
    
    // Choose the appropriate API key based on the mode
    global $apiKeyNormal, $apiKeyEasy;
    if ($mode === 'easy') {
        $apiKey = $apiKeyEasy; // Easy mode API key
        $url = "https://www.dictionaryapi.com/api/v3/references/sd2/json/{$word}?key={$apiKey}";
    } else {
        $apiKey = $apiKeyNormal; // Normal mode API key
        $url = "https://www.dictionaryapi.com/api/v3/references/sd4/json/{$word}?key={$apiKey}";
    }
    
    // Fetch definition for this word
    $response = @file_get_contents($url);
    
    if ($response === false) {
        return false;
    }
    
    $definitionData = json_decode($response, true);
    
    if (empty($definitionData) || !is_array($definitionData) || !isset($definitionData[0]['shortdef'])) {
        return false;
    }
    
    $definition = isset($definitionData[0]['shortdef']) && is_array($definitionData[0]['shortdef']) 
        ? implode('; ', $definitionData[0]['shortdef']) 
        : 'Definition not found for this word.';
    
    return [
        'word' => $word,
        'definition' => $definition
    ];
}

// Get the mode from the query parameter (default to 'normal')
$mode = isset($_GET['mode']) && in_array($_GET['mode'], ['easy', 'normal']) ? $_GET['mode'] : 'normal';

// Main logic: Get the word and definition from the API
$result = getWordAndDefinition($mode);

// If the API fails, fallback to file-based method
if ($result === false) {
    $result = getWordAndDefinitionFromFile($mode);
    if ($result === false) {
        die('Error: Unable to retrieve a word and definition from either API or file.');
    }
}

// Store the word and definition in the session
$_SESSION['real_word'] = $result['word'];
$_SESSION['word_definition'] = $result['definition'];

// Optional: Output as JSON
echo json_encode([
    'word' => $_SESSION['real_word'],
    'definition' => $_SESSION['word_definition']
]);
?>
