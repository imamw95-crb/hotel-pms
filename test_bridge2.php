<?php
// Test koneksi
$url = 'http://192.168.88.2:8080/bridge_api.php?action=test';
echo "=== TEST KONEKSI ===\n";
$r = file_get_contents($url);
echo $r . "\n\n";

// Baca kartu
$url = 'http://192.168.88.2:8080/bridge_api.php?action=read';
echo "=== BACA KARTU ===\n";
$r = file_get_contents($url);
echo $r . "\n\n";

// Coba new_checkin (I) yang otomatis checkout existing + checkin baru
$url = 'http://192.168.88.2:8080/bridge_api.php?action=new_checkin&room=0107&name=TestVia107&checkin=202606091400&checkout=202606101200';
echo "=== NEW CHECKIN 0107 ===\n";
$r = file_get_contents($url);
echo $r . "\n";
