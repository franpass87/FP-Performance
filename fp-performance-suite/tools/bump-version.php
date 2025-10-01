<?php
declare(strict_types=1);

$pluginFile = dirname(__DIR__) . '/fp-performance-suite.php';

function usage(): void
{
    fwrite(STDERR, "Usage: php tools/bump-version.php [--set=<version>] [--bump=patch|minor|major] [--patch|--minor|--major]\n");
    exit(1);
}

$longopts = ['set:', 'bump:', 'major', 'minor', 'patch', 'help'];
$options = getopt('', $longopts, $optind);

if ($options === false || isset($options['help'])) {
    usage();
}

if (!file_exists($pluginFile) || !is_file($pluginFile)) {
    fwrite(STDERR, "Plugin file not found: {$pluginFile}\n");
    exit(1);
}

$setVersion = $options['set'] ?? null;

$explicitBump = $options['bump'] ?? null;
if ($explicitBump !== null && !in_array($explicitBump, ['patch', 'minor', 'major'], true)) {
    fwrite(STDERR, "Invalid --bump value. Allowed: patch, minor, major.\n");
    exit(1);
}

$bumpType = $explicitBump ?? 'patch';
if (isset($options['major'])) {
    $bumpType = 'major';
}
if (isset($options['minor'])) {
    $bumpType = 'minor';
}
if (isset($options['patch'])) {
    $bumpType = 'patch';
}

$contents = file_get_contents($pluginFile);
if ($contents === false) {
    fwrite(STDERR, "Unable to read plugin file.\n");
    exit(1);
}

$pattern = '/^(?P<prefix>\s*\*\s*Version:\s*)(?P<version>[^\r\n]+)/mi';
if (!preg_match($pattern, $contents, $matches, PREG_OFFSET_CAPTURE)) {
    fwrite(STDERR, "Version line not found in plugin header.\n");
    exit(1);
}

$currentVersion = trim($matches['version'][0]);
$newVersion = null;

if ($setVersion !== null) {
    if (!preg_match('/^\d+\.\d+\.\d+$/', $setVersion)) {
        fwrite(STDERR, "Invalid --set version. Expected semantic version (e.g., 1.2.3).\n");
        exit(1);
    }
    $newVersion = $setVersion;
} else {
    if (!preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $currentVersion, $versionParts)) {
        fwrite(STDERR, "Current version is not in semantic version format.\n");
        exit(1);
    }

    $major = (int) $versionParts[1];
    $minor = (int) $versionParts[2];
    $patch = (int) $versionParts[3];

    switch ($bumpType) {
        case 'major':
            $major++;
            $minor = 0;
            $patch = 0;
            break;
        case 'minor':
            $minor++;
            $patch = 0;
            break;
        case 'patch':
        default:
            $patch++;
            break;
    }

    $newVersion = sprintf('%d.%d.%d', $major, $minor, $patch);
}

$updatedContents = preg_replace_callback(
    $pattern,
    static function (array $match) use ($newVersion) {
        return $match['prefix'] . $newVersion;
    },
    $contents,
    1,
    $replacements
);

if ($updatedContents === null || $replacements === 0) {
    fwrite(STDERR, "Failed to update version line.\n");
    exit(1);
}

$constantPattern = "/(define\('FP_PERF_SUITE_VERSION',\s*')([^']+)('\);)/";
$updatedContents = preg_replace_callback(
    $constantPattern,
    static function (array $match) use ($newVersion) {
        return $match[1] . $newVersion . $match[3];
    },
    $updatedContents,
    1,
    $constantReplacements
);

if ($updatedContents === null) {
    fwrite(STDERR, "Failed to update version constant.\n");
    exit(1);
}

if ($constantReplacements === 0) {
    fwrite(STDERR, "Warning: version constant not found for update.\n");
}

if (file_put_contents($pluginFile, $updatedContents) === false) {
    fwrite(STDERR, "Unable to write updated plugin file.\n");
    exit(1);
}

echo $newVersion, PHP_EOL;
