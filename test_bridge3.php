<?php
// 1. Register encoder
echo "=== REGISTER ENCODER ===\n";
$r = file_get_contents('http://192.168.88.2:8080/bridge_api.php?action=register_encoder&ip=192.168.88.2');
echo $r . "\n\n";

// 2. Test lagi
echo "=== TEST SETELAH REGISTER ===\n";
$r = file_get_contents('http://192.168.88.2:8080/bridge_api.php?action=test');
echo $r . "\n\n";

// 3. Coba checkin lagi
echo "=== CHECKIN 0107 ===\n";
$r = file_get_contents('http://192.168.88.2:8080/bridge_api.php?action=checkin&room=0107&name=TestFinal&checkin=202606091400&checkout=202606101200');
echo $r . "\n";
