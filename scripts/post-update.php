<?php

// scripts/post-update.php

/**
 * Custom Composer post-update script for Career Compass.
 * Handles environment-specific actions for existing installations (dev/prod).
 * This script runs after 'composer update' and also after 'composer install'
 * if the .env file already exists (i.e., not a fresh clone).
 */

$rootPath = dirname(__DIR__);
$envFilePath = $rootPath . '/.env';

// This script *requires* the .env file to exist to determine the environment.
if (!file_exists($envFilePath)) {
    echo "ERROR: .env file not found. Cannot determine APP_ENV for post-update actions.\n";
    echo "Please ensure your .env file is present or run 'composer install' for initial setup.\n";
    exit(1);
}

$appEnv = null;
$lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
        continue;
    }
    list($name, $value) = explode('=', $line, 2);
    $name = trim($name);
    $value = trim($value);
    if (preg_match('/^"(.+)"$/', $value, $matches) || preg_match("/^'(.+)'$/", $value, $matches)) {
        $value = $matches[1];
    }
    putenv("{$name}={$value}");
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
    if ($name === 'APP_ENV') {
        $appEnv = $value;
        break;
    }
}

// Fallback to system env or 'local' default
if ($appEnv === null) {
    $appEnv = getenv('APP_ENV') ?: (isset($_SERVER['APP_ENV']) ? $_SERVER['APP_ENV'] : 'local');
}

echo "=========================================================\n";
echo "Detected APP_ENV: " . $appEnv . "\n";
echo "Running environment-specific Composer update scripts...\n";
echo "=========================================================\n";

// --- Commands common to all existing environments (dev/prod) after an update ---
passthru('php artisan package:discover --ansi');
passthru('php artisan filament:upgrade');
passthru('php artisan vendor:publish --tag=laravel-assets --ansi --force');

// --- Environment-specific commands ---
if ($appEnv === 'production') {
    echo "Executing PRODUCTION-specific commands...\n";
    passthru('php artisan migrate --force');
    passthru('php artisan optimize:clear');
    passthru('php artisan optimize');
    passthru('php artisan queue:restart');

} elseif ($appEnv === 'local' || $appEnv === 'development') {
    echo "Executing DEVELOPMENT/LOCAL-specific commands...\n";
    passthru('php artisan migrate');
    passthru('php artisan optimize:clear');

} else {
    echo "Executing GENERIC commands for environment: " . $appEnv . "\n";
    passthru('php artisan migrate');
    passthru('php artisan optimize:clear');
}

passthru('php artisan storage:link');