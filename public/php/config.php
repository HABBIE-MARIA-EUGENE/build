<?php
//require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
//require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// MySQL
try {
    $dsn = "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']};charset=utf8mb4";
    $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (Exception $e) {
    throw new RuntimeException("MySQL connection failed: " . $e->getMessage());
}

// Redis
function getRedis(): Redis {
    $redis = new Redis();
    $ctx = ['ssl' => ['SNI_enabled' => true, 'peer_name' => $_ENV['REDIS_HOST']]];
    $redis->connect($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT'], 3.0, null, 0, 0, $_ENV['REDIS_USE_TLS'] === "true" ? $ctx : null);
    if (!$redis->auth([$_ENV['REDIS_USER'], $_ENV['REDIS_PASS']])) {
        throw new RuntimeException("Redis authentication failed");
    }
    return $redis;
}

// MongoDB
function getMongo(): MongoDB\Client {
    return new MongoDB\Client($_ENV['MONGO_URI'], ['ssl' => true]);
}
