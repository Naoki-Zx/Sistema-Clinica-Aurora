<?php
/**
 * Conexão PDO singleton — prepared statements obrigatórios nas queries.
 * charset utf8mb4 e exceções habilitadas para falhas visíveis em desenvolvimento.
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$configPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config.ini';

if (!is_readable($configPath)) {
    http_response_code(500);
    exit('Arquivo config.ini não encontrado.');
}

$conf = parse_ini_file($configPath);

if ($conf === false) {
    http_response_code(500);
    exit('Não foi possível ler config.ini.');
}

$dsn = sprintf(
    '%s:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $conf['driver']   ?? 'mysql',
    $conf['server']   ?? 'localhost',
    $conf['port']     ?? '3306',
    $conf['database'] ?? 'clinica_aurora'
);

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO(
        $dsn,
        $conf['user'] ?? 'root',
        $conf['password'] ?? '',
        $options
    );
} catch (PDOException $e) {
    http_response_code(500);
    $debug = ($conf['debug'] ?? 'false') === 'true';
    exit($debug
        ? 'Erro de conexão: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
        : 'Não foi possível conectar ao banco de dados. Verifique config.ini e o schema.sql.');
}

require_once __DIR__ . '/helpers.php';
