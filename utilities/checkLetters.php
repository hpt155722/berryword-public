<?php
// Start the session
session_start();

// Check if the real word exists in the session
if (!isset($_SESSION['real_word'])) {
    echo json_encode(['error' => 'Real word not found.']);
    exit();
}


// Get the real word from the session
$realWord = $_SESSION['real_word'];

// Get the guessed word from the query parameter
$guessedWord = strtoupper($_GET['word']);  // Ensure it's uppercase
$feedback = [];


if (strlen($guessedWord) !== 5) {
  echo json_encode(['error' => 'Invalid word length.']);
  exit();
}


if (strlen($guessedWord) !== 5) {
    echo json_encode(['error' => 'Invalid word length.']);
    exit();
}

// Compare each letter of the guessed word with the real word
for ($i = 0; $i < 5; $i++) {
    $letter = $guessedWord[$i];
    if ($letter === $realWord[$i]) {
        $feedback[] = 'correct';
    } elseif (strpos($realWord, $letter) !== false) {
        $feedback[] = 'inWord';
    } else {
        $feedback[] = 'notInWord';
    }
}

// Return the feedback as a JSON response
echo json_encode(['feedback' => $feedback]);
?>
