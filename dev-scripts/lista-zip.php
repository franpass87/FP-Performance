<?php
$zip = new ZipArchive();
$zip->open('fp-performance-suite.zip');

echo "File nello ZIP (primi 30):\n\n";

for ($i = 0; $i < min(30, $zip->numFiles); $i++) {
    $stat = $zip->statIndex($i);
    echo $stat['name'] . "\n";
}

$zip->close();

