<?php
session_start(); // Start the session

// Check if the session variables 'real_word' and 'word_definition' are set
if (isset($_SESSION['real_word']) && isset($_SESSION['word_definition'])) {
    // Return both the 'real_word' and 'word_definition' values as a JSON object
    echo json_encode([
        'word' => $_SESSION['real_word'],
        'definition' => $_SESSION['word_definition']
    ]);
} else {
    // If the session variables are not set, return an error message
    echo json_encode(['error' => 'No real word or definition found in session.']);
}
?>
