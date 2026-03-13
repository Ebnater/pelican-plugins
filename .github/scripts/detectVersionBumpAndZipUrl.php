<?php

/**
 * Detects a version bump between plugin.json and update.json and prints a ZIP URL.
 *
 * Usage:
 *   php .github/scripts/detectVersionBumpAndZipUrl.php <plugin-directory> <download-base-url>
 *
 * Example:
 *   php .github/scripts/detectVersionBumpAndZipUrl.php backup-templates https://github.com/Ebnater/pelican-plugins/releases/download
 */

if ($argc < 3) {
    fwrite(STDERR, "Usage: php .github/scripts/detectVersionBumpAndZipUrl.php <plugin-directory> <download-base-url>\n");
    exit(1);
}

$pluginDirectory = rtrim($argv[1], "\\/");
$downloadBaseUrl = rtrim($argv[2], '/');

if (!is_dir($pluginDirectory)) {
    fwrite(STDERR, "Directory not found: {$pluginDirectory}\n");
    exit(1);
}

if (!filter_var($downloadBaseUrl, FILTER_VALIDATE_URL)) {
    fwrite(STDERR, "Invalid URL: {$downloadBaseUrl}\n");
    exit(1);
}

$pluginJsonPath = $pluginDirectory . DIRECTORY_SEPARATOR . 'plugin.json';
$updateJsonPath = $pluginDirectory . DIRECTORY_SEPARATOR . 'update.json';

if (!is_file($pluginJsonPath)) {
    fwrite(STDERR, "Missing file: {$pluginJsonPath}\n");
    exit(1);
}

if (!is_file($updateJsonPath)) {
    fwrite(STDERR, "Missing file: {$updateJsonPath}\n");
    exit(1);
}

/**
 * @return array<string, mixed>
 */
function readJsonFile(string $path): array
{
    $content = file_get_contents($path);

    if ($content === false) {
        fwrite(STDERR, "Failed to read: {$path}\n");
        exit(1);
    }

    $decoded = json_decode($content, true);

    if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
        fwrite(STDERR, "Invalid JSON in {$path}: " . json_last_error_msg() . "\n");
        exit(1);
    }

    return $decoded;
}

/**
 * @param array<string, mixed> $pluginJson
 * @param array<string, mixed> $updateJson
 *
 * @return array{pluginId: string, pluginVersion: string, updateVersion: string}
 */
function extractVersionData(array $pluginJson, array $updateJson): array
{
    $pluginId = $pluginJson['id'] ?? null;
    $pluginVersion = $pluginJson['version'] ?? null;
    $updateVersion = $updateJson['*']['version'] ?? null;

    if (!is_string($pluginId) || $pluginId === '') {
        fwrite(STDERR, "plugin.json must contain a non-empty string field 'id'.\n");
        exit(1);
    }

    if (!is_string($pluginVersion) || $pluginVersion === '') {
        fwrite(STDERR, "plugin.json must contain a non-empty string field 'version'.\n");
        exit(1);
    }

    if (!is_string($updateVersion) || $updateVersion === '') {
        fwrite(STDERR, "update.json must contain a non-empty string field '*.version'.\n");
        exit(1);
    }

    return [
        'pluginId' => $pluginId,
        'pluginVersion' => $pluginVersion,
        'updateVersion' => $updateVersion,
    ];
}

/**
 * Writes values for GitHub Actions output if available.
 *
 * @param array<string, string> $values
 */
function writeGithubOutput(array $values): void
{
    $githubOutput = getenv('GITHUB_OUTPUT');

    if (!is_string($githubOutput) || $githubOutput === '') {
        return;
    }

    $lines = [];

    foreach ($values as $key => $value) {
        $lines[] = $key . '=' . $value;
    }

    file_put_contents($githubOutput, implode(PHP_EOL, $lines) . PHP_EOL, FILE_APPEND);
}

$pluginJson = readJsonFile($pluginJsonPath);
$updateJson = readJsonFile($updateJsonPath);
$data = extractVersionData($pluginJson, $updateJson);

$pluginId = $data['pluginId'];
$pluginVersion = $data['pluginVersion'];
$updateVersion = $data['updateVersion'];

$comparison = version_compare($pluginVersion, $updateVersion);

if ($comparison <= 0) {
    echo "No version bump detected.\n";
    echo "Current plugin.json version: {$pluginVersion}\n";
    echo "Current update.json version: {$updateVersion}\n";

    writeGithubOutput([
        'bump_detected' => 'false',
        'plugin_id' => $pluginId,
        'new_version' => $pluginVersion,
        'old_version' => $updateVersion,
        'zip_url' => '',
    ]);

    exit(0);
}

$artifactName = "{$pluginId}-{$pluginVersion}";
$zipUrl = "{$downloadBaseUrl}/{$artifactName}/{$artifactName}.zip";

echo "Version bump detected: {$updateVersion} -> {$pluginVersion}\n";
echo "ZIP URL: {$zipUrl}\n";

writeGithubOutput([
    'bump_detected' => 'true',
    'plugin_id' => $pluginId,
    'new_version' => $pluginVersion,
    'old_version' => $updateVersion,
    'zip_url' => $zipUrl,
]);

exit(0);