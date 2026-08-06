<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

class QueryBuilder
{
    private PDO $pdo;
    private string $table = '';
    private array $columns = ['*'];
    private array $wheres = [];
    private array $whereBindings = [];
    private array $joins = [];
    private array $orderBys = [];
    private ?int $limitVal = null;
    private ?int $offsetVal = null;
    private array $groups = [];
    private array $havings = [];
    private array $havingBindings = [];
    private bool $distinct = false;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function from(string $table): self
    {
        $clone = clone $this;
        $clone->table = $table;
        return $clone;
    }

    public function select(string|RawExpression ...$columns): self
    {
        $clone = clone $this;
        $clone->columns = $columns;
        return $clone;
    }

    public function distinct(): self
    {
        $clone = clone $this;
        $clone->distinct = true;
        return $clone;
    }

    public function where(string $column, mixed $operatorOrValue, mixed $value = null): self
    {
        $clone = clone $this;
        if ($value === null) {
            $value = $operatorOrValue;
            $operatorOrValue = '=';
        }
        $clone->wheres[] = ['AND', $column, $operatorOrValue];
        $clone->whereBindings[] = $value;
        return $clone;
    }

    public function orWhere(string $column, mixed $operatorOrValue, mixed $value = null): self
    {
        $clone = clone $this;
        if ($value === null) {
            $value = $operatorOrValue;
            $operatorOrValue = '=';
        }
        $clone->wheres[] = ['OR', $column, $operatorOrValue];
        $clone->whereBindings[] = $value;
        return $clone;
    }

    public function whereNull(string $column): self
    {
        $clone = clone $this;
        $clone->wheres[] = ['AND', $column, 'IS_NULL'];
        return $clone;
    }

    public function whereNotNull(string $column): self
    {
        $clone = clone $this;
        $clone->wheres[] = ['AND', $column, 'IS_NOT_NULL'];
        return $clone;
    }

    public function whereIn(string $column, array $values): self
    {
        $clone = clone $this;
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $clone->wheres[] = ['AND', $column, "IN({$placeholders})"];
        $clone->whereBindings = array_merge($clone->whereBindings, array_values($values));
        return $clone;
    }

    public function whereNotIn(string $column, array $values): self
    {
        $clone = clone $this;
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $clone->wheres[] = ['AND', $column, "NOT_IN({$placeholders})"];
        $clone->whereBindings = array_merge($clone->whereBindings, array_values($values));
        return $clone;
    }

    public function whereRaw(string $expression, array $bindings = []): self
    {
        $clone = clone $this;
        $clone->wheres[] = ['AND_RAW', $expression];
        $clone->whereBindings = array_merge($clone->whereBindings, $bindings);
        return $clone;
    }

    public function whereBetween(string $column, mixed $start, mixed $end): self
    {
        $clone = clone $this;
        $clone->wheres[] = ['AND', $column, 'BETWEEN'];
        $clone->whereBindings[] = $start;
        $clone->whereBindings[] = $end;
        return $clone;
    }

    public function when(mixed $condition, callable $callback): self
    {
        if ($condition) {
            return $callback($this, $condition);
        }
        return $this;
    }

    public function join(string $table, string $col1, string $operator, string $col2, string $type = 'INNER'): self
    {
        $clone = clone $this;
        $clone->joins[] = "{$type} JOIN {$table} ON {$col1} {$operator} {$col2}";
        return $clone;
    }

    public function leftJoin(string $table, string $col1, string $operator, string $col2): self
    {
        return $this->join($table, $col1, $operator, $col2, 'LEFT');
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $clone = clone $this;
        $clone->orderBys[] = "{$column} " . strtoupper($direction);
        return $clone;
    }

    public function orderByDesc(string $column): self
    {
        return $this->orderBy($column, 'DESC');
    }

    public function limit(int $limit): self
    {
        $clone = clone $this;
        $clone->limitVal = $limit;
        return $clone;
    }

    public function offset(int $offset): self
    {
        $clone = clone $this;
        $clone->offsetVal = $offset;
        return $clone;
    }

    public function groupBy(string ...$columns): self
    {
        $clone = clone $this;
        $clone->groups = array_merge($clone->groups, $columns);
        return $clone;
    }

    public function having(string $column, string $operator, mixed $value): self
    {
        $clone = clone $this;
        $clone->havings[] = "{$column} {$operator} ?";
        $clone->havingBindings[] = $value;
        return $clone;
    }

    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $totalQuery = clone $this;
        $totalQuery->columns = ['COUNT(*) as total'];
        $totalQuery->limitVal = null;
        $totalQuery->offsetVal = null;
        $totalQuery->orderBys = [];
        $total = (int) $totalQuery->first()->total;

        $clone = clone $this;
        $clone->limitVal = $perPage;
        $clone->offsetVal = ($page - 1) * $perPage;

        $results = $clone->get();

        $lastPage = (int) ceil($total / $perPage);

        return [
            'data' => $results,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage,
            'from' => $total > 0 ? ($page - 1) * $perPage + 1 : 0,
            'to' => min($page * $perPage, $total),
            'has_more_pages' => $page < $lastPage,
            'has_pages' => $lastPage > 1,
        ];
    }

    public function get(): array
    {
        [$sql, $bindings] = $this->buildSelect();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    public function first(): ?object
    {
        $clone = clone $this;
        $clone->limitVal = 1;
        $results = $clone->get();
        return $results[0] ?? null;
    }

    public function value(string $column): mixed
    {
        $clone = clone $this;
        $clone->columns = [$column];
        $row = $clone->first();
        return $row ? $row->{$column} ?? reset((array) $row) : null;
    }

    public function count(): int
    {
        $clone = clone $this;
        $clone->columns = ['COUNT(*) as aggregate'];
        $clone->limitVal = null;
        $clone->offsetVal = null;
        $clone->orderBys = [];
        $row = $clone->first();
        return (int) ($row->aggregate ?? 0);
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function sum(string $column): float
    {
        $clone = clone $this;
        $clone->columns = ["SUM({$column}) as aggregate"];
        $clone->limitVal = null;
        $clone->offsetVal = null;
        $row = $clone->first();
        return (float) ($row->aggregate ?? 0);
    }

    public function pluck(string $column, ?string $key = null): array
    {
        $results = $this->get();
        $plucked = [];
        foreach ($results as $row) {
            $k = $key ? $row->{$key} : count($plucked);
            $plucked[$k] = $row->{$column};
        }
        return $plucked;
    }

    public function insert(array $data): int
    {
        if (!isset($data[0])) {
            $data = [$data];
        }

        $columns = array_keys($data[0]);
        $columnNames = implode(', ', array_map(fn($c) => "`{$c}`", $columns));

        $rows = [];
        $bindings = [];
        foreach ($data as $row) {
            $placeholders = [];
            foreach ($columns as $col) {
                $placeholders[] = '?';
                $bindings[] = $row[$col] ?? null;
            }
            $rows[] = '(' . implode(', ', $placeholders) . ')';
        }

        $sql = "INSERT INTO `{$this->table}` ({$columnNames}) VALUES " . implode(', ', $rows);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(array $data): int
    {
        $setParts = [];
        $bindings = [];
        foreach ($data as $column => $value) {
            $setParts[] = "`{$column}` = ?";
            $bindings[] = $value;
        }

        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $setParts);
        $sql .= $this->buildWhereClause();
        $bindings = array_merge($bindings, $this->whereBindings);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->rowCount();
    }

    public function delete(): int
    {
        $sql = "DELETE FROM `{$this->table}`";
        $sql .= $this->buildWhereClause();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->whereBindings);

        return $stmt->rowCount();
    }

    public function raw(string $expression, array $bindings = []): RawExpression
    {
        return new RawExpression($expression, $bindings);
    }

    public function toSql(): string
    {
        [$sql] = $this->buildSelect();
        return $sql;
    }

    private function buildSelect(): array
    {
        $sql = 'SELECT ';
        if ($this->distinct) {
            $sql .= 'DISTINCT ';
        }
        $sql .= implode(', ', array_map(fn($c) => $c instanceof RawExpression ? $c->expression : $c, $this->columns));
        $sql .= " FROM `{$this->table}`";

        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }

        if (!empty($this->wheres)) {
            $sql .= $this->buildWhereClause();
        }

        if (!empty($this->groups)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groups);
        }

        if (!empty($this->havings)) {
            $sql .= ' HAVING ' . implode(' AND ', $this->havings);
        }

        if (!empty($this->orderBys)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBys);
        }

        if ($this->limitVal !== null) {
            $sql .= " LIMIT {$this->limitVal}";
        }

        if ($this->offsetVal !== null) {
            $sql .= " OFFSET {$this->offsetVal}";
        }

        $bindings = array_merge($this->whereBindings, $this->havingBindings);

        return [$sql, $bindings];
    }

    private function buildWhereClause(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $parts = [];

        foreach ($this->wheres as $i => $where) {
            $boolean = $where[0];
            $column = $where[1];
            $operator = $where[2] ?? null;

            if ($i > 0) {
                $parts[] = $boolean === 'OR' ? 'OR' : 'AND';
            }

            if ($boolean === 'AND_RAW') {
                $parts[] = $column;
                continue;
            }

            $col = $this->wrapColumn($column);

            if ($operator === 'IS_NULL') {
                $parts[] = "{$col} IS NULL";
            } elseif ($operator === 'IS_NOT_NULL') {
                $parts[] = "{$col} IS NOT NULL";
            } elseif (str_starts_with($operator, 'IN(')) {
                $parts[] = "{$col} IN " . substr($operator, 3);
            } elseif (str_starts_with($operator, 'NOT_IN(')) {
                $parts[] = "{$col} NOT IN " . substr($operator, 7);
            } elseif ($operator === 'BETWEEN') {
                $parts[] = "{$col} BETWEEN ? AND ?";
            } else {
                $parts[] = "{$col} {$operator} ?";
            }
        }

        return ' WHERE ' . implode(' ', $parts);
    }

    private function wrapColumn(string $column): string
    {
        if (str_contains($column, '.')) {
            [$table, $col] = explode('.', $column, 2);
            return "`{$table}`.`{$col}`";
        }
        return "`{$column}`";
    }
}
