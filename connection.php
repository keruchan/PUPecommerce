<?php
declare(strict_types=1);

/*
 * Production: set these as environment variables in your hosting control panel.
 * Local XAMPP: the defaults below connect to the local little_things database.
 */
$appEnvironment = getenv('APP_ENV') ?: 'local';
$databaseVariables = ['DB_HOST', 'DB_NAME', 'DB_USERNAME', 'DB_PASSWORD', 'DB_PORT'];

if ($appEnvironment === 'production') {
    foreach ($databaseVariables as $variable) {
        if (getenv($variable) === false) {
            throw new RuntimeException("Missing required environment variable: {$variable}");
        }
    }
}

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'u763192172_pupecommerce';
$dbUser = getenv('DB_USERNAME') ?: 'u763192172_pupecommerce';
$dbPass = getenv('DB_PASSWORD'); ?: 'Pupcommerce1';
$dbPass = $dbPass === false ? '' : $dbPass;
$dbPort = getenv('DB_PORT') ?: '3306';
$dbCharset = 'utf8mb4';

$validatedPort = filter_var($dbPort, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 65535],
]);
if ($validatedPort === false) {
    throw new RuntimeException('DB_PORT must be a number from 1 to 65535.');
}

$dsn = "mysql:host={$dbHost};port={$validatedPort};dbname={$dbName};charset={$dbCharset}";
$db = new PDO($dsn, $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
