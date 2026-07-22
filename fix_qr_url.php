<?php
$files = [
    'resources/views/reservations/print-invoice.blade.php',
    'resources/views/reservations/print-group-invoice.blade.php',
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $old = 'url(\'/invoice/\' . ';
    $new = "config('app.url') . '/invoice/' . ";
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo "$file OK\n";
}
