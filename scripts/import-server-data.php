<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/process-repository.php';

/**
 * @return array<int, string>
 */
function ndImportListJsonFiles(string $sourceDir): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $item) {
        if (!$item instanceof SplFileInfo || !$item->isFile()) {
            continue;
        }

        if (strtolower($item->getExtension()) !== 'json') {
            continue;
        }

        $files[] = $item->getPathname();
    }

    sort($files);
    return $files;
}

function ndImportCollectionFromPath(string $sourceDir, string $absoluteFile): string
{
    $relativePath = ltrim(substr($absoluteFile, strlen(rtrim($sourceDir, '/'))), '/');
    $segments = explode('/', str_replace('\\', '/', $relativePath));
    if (count($segments) > 1) {
        $candidate = strtolower(trim($segments[0]));
        if (preg_match('/^[a-z0-9_-]+$/', $candidate)) {
            return $candidate;
        }
    }

    return 'legacy';
}

function ndImportHandleFromFile(string $absoluteFile): string
{
    $fileName = strtolower(pathinfo($absoluteFile, PATHINFO_FILENAME));
    $sanitized = preg_replace('/[^a-z0-9_-]/', '-', $fileName);
    $sanitized = is_string($sanitized) ? trim($sanitized, '-') : '';
    if ($sanitized === '') {
        return '';
    }

    return $sanitized;
}

function ndImportUsage(): void
{
    fwrite(STDERR, "Usage: php scripts/import-server-data.php --source=/absolute/path/to/export [--dry-run]\n");
}

$sourceDir = '';
$dryRun = false;

foreach (array_slice($argv, 1) as $arg) {
    if (strpos($arg, '--source=') === 0) {
        $sourceDir = trim(substr($arg, 9));
        continue;
    }

    if ($arg === '--dry-run') {
        $dryRun = true;
    }
}

if ($sourceDir === '' || $sourceDir[0] !== '/') {
    ndImportUsage();
    exit(1);
}

$resolvedSourceDir = realpath($sourceDir);
if ($resolvedSourceDir === false || !is_dir($resolvedSourceDir)) {
    fwrite(STDERR, "Source directory not found: {$sourceDir}\n");
    exit(1);
}

$jsonFiles = ndImportListJsonFiles($resolvedSourceDir);
if ($jsonFiles === []) {
    fwrite(STDOUT, "No JSON files found in {$resolvedSourceDir}.\n");
    exit(0);
}

$mapping = [];
$errors = [];

foreach ($jsonFiles as $filePath) {
    $collection = ndImportCollectionFromPath($resolvedSourceDir, $filePath);
    $handle = ndImportHandleFromFile($filePath);

    if ($handle === '') {
        $errors[] = "Handle could not be derived from file: {$filePath}";
        continue;
    }

    [$decoded, $loadError] = ndRepoLoadJsonFile($filePath);
    if ($decoded === null) {
        $errors[] = "{$filePath}: {$loadError}";
        continue;
    }

    $targetPath = ndRepoBuildPath($collection, $handle);
    if ($targetPath === '') {
        $errors[] = "Invalid target path for {$filePath}";
        continue;
    }

    if (!$dryRun) {
        [$saved, $saveError] = ndRepoWriteJsonAtomically($targetPath, $decoded);
        if (!$saved) {
            $errors[] = "{$filePath}: {$saveError}";
            continue;
        }
    }

    $mapping[] = [
        'old' => str_replace($resolvedSourceDir . '/', '', $filePath),
        'new' => $collection . '/' . $handle,
    ];
}

fwrite(STDOUT, "Migration mapping (old -> new):\n");
foreach ($mapping as $row) {
    fwrite(STDOUT, "- {$row['old']} -> {$row['new']}\n");
}

if ($errors !== []) {
    fwrite(STDERR, "\nErrors:\n");
    foreach ($errors as $error) {
        fwrite(STDERR, "- {$error}\n");
    }
    exit(1);
}

exit(0);
