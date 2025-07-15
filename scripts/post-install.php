<?php

// scripts/post-install.php

/**
 * Custom Composer post-install script for Career Compass.
 * Specifically handles initial project setup when no .env file exists.
 * For subsequent installs/updates, scripts/post-update.php is used for env-specific logic.
 */

$rootPath = dirname(__DIR__);
$envFilePath = $rootPath . '/.env';

// This script primarily acts on fresh installs where .env doesn't exist.
// If .env already exists, it's typically an update or subsequent install,
// which will be handled by post-update.php or manual commands.
if (!file_exists($envFilePath)) {
    echo "=========================================================\n";
    echo "No .env file found. Performing initial project setup...\n";
    echo "=========================================================\n";

    // Original commands from "post-root-package-install" and "post-create-project-cmd"
    // These ensure the basic environment is ready for first-time users.
    passthru('php -r "file_exists(\'.env\') || copy(\'.env.example\', \'.env\');"');
    passthru('php artisan key:generate --ansi');
    passthru('php -r "file_exists(\'database/database.sqlite\') || touch(\'database/database.sqlite\');"');

    // Run migrations and seeders for a fresh install (from original post-install-cmd)
    passthru('php artisan migrate --seed --force'); // --force added for non-interactive initial setup
    passthru('php artisan storage:link'); // From original post-install-cmd

    echo "Initial setup complete. Please configure your .env file and re-run 'composer install' or 'composer update' if needed.\n";
    exit(0); // Exit immediately as initial setup is done.
}

// If .env exists, this script might be called but its primary logic is for initial setup.
// For existing environments, the post-update.php script will contain the main logic.
echo "=========================================================\n";
echo "'.env' file found. Skipping initial setup in post-install.php.\n";
echo "Environment-specific commands will be handled by post-update.php or manual intervention.\n";
echo "=========================================================\n";

// Potentially add some common commands that *always* run after an install,
// even if .env exists, but are not env-specific (e.g., clearing some caches after vendor changes)
// For now, keeping it minimal to distinguish from post-update.php
passthru('php artisan package:discover --ansi'); 
passthru('php artisan filament:upgrade'); 