<?php
// test_api_check.php
// Simulates a GET request to departments.php

$_SERVER['REQUEST_METHOD'] = 'GET';

// Capture output
ob_start();
require 'backend/api/departments.php';
$output = ob_get_clean();

echo "--- RAW OUTPUT START ---\n";
echo $output;
echo "\n--- RAW OUTPUT END ---\n";

$json = json_decode($output, true);
if ($json) {
    echo "JSON Decode: SUCCESS\n";
    print_r($json);
} else {
    echo "JSON Decode: FAILED\n";
    echo "Last JSON Error: " . json_last_error_msg() . "\n";
}
?>
