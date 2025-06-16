<?php
// Check if asset ID is provided
if (!isset($_GET['id'])) {
    die('Asset ID is required.');
}

// Define the API URL for retrieving asset details
$api_url = "http://your-domain.com/api/get-asset.php?id=1" . intval($_GET['id']); // Adjust this to your API endpoint

// Prepare the QR Code API URL with parameters
$qr_api_url = 'https://api.qrserver.com/v1/create-qr-code/';
$params = http_build_query([
    'data' => $api_url, // The data to encode
    'size' => '100x100', // Size of the QR code
    'format' => 'png'    // Format of the output
]);

// Final URL for the QR code
$qr_code_url = $qr_api_url . '?' . $params;

// Output the QR code image
header('Content-Type: image/png');
readfile($qr_code_url); // Fetch the QR code image from the API and output it
?>