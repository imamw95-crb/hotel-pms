<?php
$url = 'http://192.168.88.2:8080/bridge_api.php?action=checkin&room=0107&name=TestGuest&checkin=202606091400&checkout=202606101200';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);
echo 'HTTP: ' . $httpCode . PHP_EOL;
echo 'Error: ' . ($error ?: 'none') . PHP_EOL;
echo 'Response: ' . $response . PHP_EOL;
