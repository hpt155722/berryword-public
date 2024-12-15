<?php
session_start(); // Start the session

// Check if the session variable 'real_word' is set
if (isset($_SESSION['real_word'])) {
    // Return the 'real_word' value
    echo $_SESSION['real_word'];
} else {
    // If the session variable is not set, return an error message
    echo 'No real word found in session.';
}
?>
