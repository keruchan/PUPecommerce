<?php
declare(strict_types=1);

/* Local XAMPP defaults. Replace these with the credentials from your host
 * when you deploy the project. Import database.sql into this database first.
 */
$dbHost = '127.0.0.1';
$dbName = 'little_things';
$dbUser = 'root';
$dbPass = '';
$dbCharset = 'utf8mb4';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";
$db = new PDO($dsn, $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
