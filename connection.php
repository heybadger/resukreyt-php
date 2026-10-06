<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'resume_builder';

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// https://makersuite.google.com/app/apikey
define('GEMINI_API_KEY', 'GEMINI_API_KEY');
?>