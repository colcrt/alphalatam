<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;

class Connection
{
    private static ?PDO $pdo = null;
    private static ?QueryBuilder $queryBuilder = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $config = require BASE_PATH . '/config/database.php';
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$pdo = new PDO($dsn, $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                throw new \RuntimeException('Error de conexión a la base de datos: ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }

    public static function table(string $table): QueryBuilder
    {
        return (new QueryBuilder(self::pdo()))->from($table);
    }

    public static function raw(string $expression, array $bindings = []): RawExpression
    {
        return new RawExpression($expression, $bindings);
    }

    public static function transaction(callable $callback): mixed
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();

        try {
            $result = $callback();
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function select(string $query, array $bindings = []): array
    {
        $stmt = self::pdo()->prepare($query);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    public static function selectOne(string $query, array $bindings = []): ?object
    {
        $stmt = self::pdo()->prepare($query);
        $stmt->execute($bindings);
        return $stmt->fetch() ?: null;
    }

    public static function insert(string $query, array $bindings = []): int
    {
        $stmt = self::pdo()->prepare($query);
        $stmt->execute($bindings);
        return (int) self::pdo()->lastInsertId();
    }

    public static function update(string $query, array $bindings = []): int
    {
        $stmt = self::pdo()->prepare($query);
        $stmt->execute($bindings);
        return $stmt->rowCount();
    }

    public static function delete(string $query, array $bindings = []): int
    {
        $stmt = self::pdo()->prepare($query);
        $stmt->execute($bindings);
        return $stmt->rowCount();
    }

    public static function getDatabaseName(): string
    {
        $config = require BASE_PATH . '/config/database.php';
        return $config['database'];
    }
}
