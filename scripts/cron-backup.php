<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/env.php';

use App\Database\Connection;

$backupPath = BASE_PATH . '/storage/backups';
if (!is_dir($backupPath)) {
    mkdir($backupPath, 0755, true);
}

$filename = 'backup-' . date('Y-m-d_His') . '.sql';
$fullPath = $backupPath . '/' . $filename;

$pdo = Connection::pdo();
$dbName = $_ENV['DB_DATABASE'] ?? 'laravel';

$tables = $pdo->query("SHOW TABLES")->fetchAll();

$sql = "-- Backup de {$dbName}\n";
$sql .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";

foreach ($tables as $table) {
    $tableName = reset($table);

    $createResult = $pdo->query("SHOW CREATE TABLE `{$tableName}`")->fetch();
    $createSql = $createResult[1] ?? $createResult['Create Table'] ?? '';

    $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
    $sql .= $createSql . ";\n\n";

    $rows = $pdo->query("SELECT * FROM `{$tableName}`")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $values = array_map(function ($v) {
            if (is_null($v)) return 'NULL';
            return "'" . addslashes((string) $v) . "'";
        }, $row);
        $sql .= "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");\n";
    }

    $sql .= "\n";
}

file_put_contents($fullPath, $sql);

$size = filesize($fullPath);
$sizeKB = number_format($size / 1024, 2);
$sizeMB = number_format($size / (1024 * 1024), 2);

// Log to activity_log
Connection::table('activity_log')->insert([
    'log_name' => 'sistema',
    'description' => "Backup generado: {$filename} ({$sizeKB} KB)",
    'subject_type' => 'App\\Models\\User',
    'subject_id' => 1,
    'properties' => json_encode(['filename' => $filename, 'size_kb' => $sizeKB]),
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

echo "✅ Backup generado: {$filename}\n";
echo "   Tamaño: {$sizeKB} KB ({$sizeMB} MB)\n";
echo "   Ruta: {$fullPath}\n";

// Clean old backups (> 30 days)
$files = glob($backupPath . '/backup-*.sql');
$cutoff = strtotime('-30 days');
$cleaned = 0;

foreach ($files as $file) {
    if (filemtime($file) < $cutoff) {
        unlink($file);
        $cleaned++;
    }
}

if ($cleaned > 0) {
    echo "   Limpieza: {$cleaned} backups antiguos eliminados.\n";
}
