<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$source = $root;
$potFile = $root . '/languages/fp-performance-suite.pot';

$functions = [
    '__',
    '_e',
    'esc_html__',
    'esc_html_e',
    'esc_attr__',
    'esc_attr_e',
    '_ex',
    'esc_html_x',
    'esc_attr_x',
];

$entries = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS)
);

/** @var SplFileInfo $file */
foreach ($iterator as $file) {
    if ($file->isDir()) {
        continue;
    }
    $ext = strtolower($file->getExtension());
    if (!in_array($ext, ['php', 'inc'], true)) {
        continue;
    }

    $code = file_get_contents($file->getPathname());
    if ($code === false) {
        continue;
    }

    $tokens = token_get_all($code);
    $length = count($tokens);
    for ($i = 0; $i < $length; $i++) {
        $token = $tokens[$i];
        if (!is_array($token)) {
            continue;
        }
        [$id, $text, $line] = $token;
        if ($id !== T_STRING || !in_array($text, $functions, true)) {
            continue;
        }

        $prev = $tokens[$i - 1] ?? null;
        if ($prev === '\\') {
            continue;
        }
        if (is_array($prev) && in_array($prev[0], [T_OBJECT_OPERATOR, T_DOUBLE_COLON], true)) {
            continue;
        }

        $j = $i + 1;
        while ($j < $length && (!is_string($tokens[$j]) || $tokens[$j] !== '(')) {
            $j++;
        }
        if ($j >= $length) {
            continue;
        }
        $j++;
        while ($j < $length && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
            $j++;
        }
        if ($j >= $length) {
            continue;
        }
        $argToken = $tokens[$j];
        if (!is_array($argToken) || $argToken[0] !== T_CONSTANT_ENCAPSED_STRING) {
            continue;
        }
        $raw = $argToken[1];
        $msgid = stripcslashes(substr($raw, 1, -1));
        if ($msgid === '') {
            continue;
        }

        $relative = str_replace($root . '/', '', $file->getPathname());
        $entries[$msgid]['references'][] = $relative . ':' . $line;
    }
}

ksort($entries, SORT_STRING);

$header = [
    'msgid ""',
    'msgstr ""',
    '"Project-Id-Version: FP Performance Suite 1.0.0\\n"',
    '"Report-Msgid-Bugs-To: \n"',
    '"POT-Creation-Date: ' . gmdate('Y-m-d H:i:s') . ' +0000\\n"',
    '"MIME-Version: 1.0\\n"',
    '"Content-Type: text/plain; charset=UTF-8\\n"',
    '"Content-Transfer-Encoding: 8bit\\n"',
    '"X-Generator: make-pot.php\\n"',
    '"X-Domain: fp-performance-suite\\n"',
    '',
];

$contents = implode("\n", $header);

foreach ($entries as $msgid => $data) {
    $references = array_unique($data['references'] ?? []);
    if ($references) {
        $contents .= '#: ' . implode(' ', $references) . "\n";
    }
    $contents .= 'msgid ' . format_po_string($msgid);
    $contents .= 'msgstr ""' . "\n\n";
}

file_put_contents($potFile, $contents);

function format_po_string(string $text): string
{
    if ($text === '') {
        return "\"\"\n";
    }
    $escaped = str_replace(
        ["\\", "\"", "\t", "\r"],
        ["\\\\", "\\\"", "\\t", "\\r"],
        $text
    );
    $escaped = str_replace("\n", "\\n\"\n\"", $escaped);
    return '"' . $escaped . '"' . "\n";
}
