<?php
declare(strict_types=1);

/*
 * Production credentials can come from hosting environment variables or the
 * private, untracked connection.secrets.php (or connection.secret.php) file.
 * Local XAMPP defaults apply
 * only when APP_ENV is not "production" and no private config is present.
 */
$privateConfig = [];
$privateConfigFile = null;
foreach ([__DIR__ . '/connection.secrets.php', __DIR__ . '/connection.secret.php'] as $candidate) {
    if (is_file($candidate)) {
        $privateConfigFile = $candidate;
        break;
    }
}
if ($privateConfigFile !== null) {
    $loadedConfig = require $privateConfigFile;
    if (!is_array($loadedConfig)) {
        throw new RuntimeException('The private database config must return an array.');
    }
    $privateConfig = $loadedConfig;
}

$readSetting = static function (string $name, ?string $default = null) use ($privateConfig): ?string {
    $environmentValue = getenv($name);
    if ($environmentValue !== false) {
        return $environmentValue;
    }
    if (array_key_exists($name, $privateConfig)) {
        return (string)$privateConfig[$name];
    }
    return $default;
};

$appEnvironment = $readSetting('APP_ENV', 'local');
$isProduction = strtolower((string)$appEnvironment) === 'production';
$localDefaults = $isProduction ? null : [
    'DB_HOST' => '127.0.0.1',
    'DB_NAME' => 'little_things',
    'DB_USERNAME' => 'root',
    'DB_PASSWORD' => '',
    'DB_PORT' => '3306',
];

$databaseSettings = [];
foreach (['DB_HOST', 'DB_NAME', 'DB_USERNAME', 'DB_PASSWORD', 'DB_PORT'] as $name) {
    $value = $readSetting($name, $localDefaults[$name] ?? null);
    if ($value === null) {
        throw new RuntimeException("Missing required production database setting: {$name}");
    }
    $databaseSettings[$name] = $value;
}

$validatedPort = filter_var($databaseSettings['DB_PORT'], FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 65535],
]);
if ($validatedPort === false) {
    throw new RuntimeException('DB_PORT must be a number from 1 to 65535.');
}

$dsn = 'mysql:host=' . $databaseSettings['DB_HOST']
    . ';port=' . $validatedPort
    . ';dbname=' . $databaseSettings['DB_NAME']
    . ';charset=utf8mb4';
$db = new PDO($dsn, $databaseSettings['DB_USERNAME'], $databaseSettings['DB_PASSWORD'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
