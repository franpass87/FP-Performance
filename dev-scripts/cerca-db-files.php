<?php
$zip = new ZipArchive();
$zip->open('fp-performance-suite.zip');

echo "=== File Services/DB nello ZIP ===\n\n";

for ($i = 0; $i < $zip->numFiles; $i++) {
    $stat = $zip->statIndex($i);
    $name = $stat['name'];
    
    if (stripos($name, 'Services') !== false && stripos($name, 'DB') !== false) {
        $size = round($stat['size'] / 1024, 1);
        echo "✅ $name ($size KB)\n";
    }
}

$zip->close();

