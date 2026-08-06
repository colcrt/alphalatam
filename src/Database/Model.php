<?php

declare(strict_types=1);

namespace App\Database;

use App\Core\Cache;

class Model
{
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $casts = [];
    protected array $dates = [];
    protected bool $timestamps = true;
    protected bool $softDeletes = false;

    public array $attributes = [];
    protected array $original = [];
    protected bool $exists = false;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        $this->castAttributes();
        return $this;
    }

    private function castAttributes(): void
    {
        foreach ($this->casts as $key => $type) {
            if (!isset($this->attributes[$key])) continue;
            $value = $this->attributes[$key];
            switch ($type) {
                case 'array':
                    $this->attributes[$key] = is_string($value) ? (json_decode($value, true) ?? []) : $value;
                    break;
                case 'date':
                    $this->attributes[$key] = ($value !== null && $value !== '') ? date('Y-m-d', strtotime((string) $value)) : null;
                    break;
                case 'datetime':
                    $this->attributes[$key] = ($value !== null && $value !== '') ? date('Y-m-d H:i:s', strtotime((string) $value)) : null;
                    break;
                case 'boolean':
                    $this->attributes[$key] = (bool) $value;
                    break;
                case 'integer':
                    $this->attributes[$key] = (int) $value;
                    break;
                case 'float':
                    $this->attributes[$key] = (float) $value;
                    break;
                default:
                    if (str_starts_with($type, 'decimal:')) {
                        $decimals = (int) substr($type, 8);
                        $this->attributes[$key] = round((float) $value, $decimals);
                    }
                    break;
            }
        }
    }

    public function save(): bool
    {
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            if (!isset($this->attributes['created_at'])) {
                $this->attributes['created_at'] = $now;
            }
            $this->attributes['updated_at'] = $now;
        }

        if ($this->exists) {
            $data = array_filter($this->attributes, fn($k) => $k !== $this->primaryKey, ARRAY_FILTER_USE_KEY);
            $this->fireEvent('updating');
            Connection::table($this->table)
                ->where($this->primaryKey, $this->attributes[$this->primaryKey] ?? 0)
                ->update($data);
            $this->fireEvent('updated');
        } else {
            $this->fireEvent('creating');
            $id = Connection::table($this->table)->insert($this->attributes);
            $this->attributes[$this->primaryKey] = $id;
            $this->exists = true;
            $this->fireEvent('created');
        }

        $this->original = $this->attributes;
        return true;
    }

    public function delete(): bool
    {
        $this->fireEvent('deleting');

        $result = false;
        if ($this->softDeletes) {
            $this->attributes['deleted_at'] = date('Y-m-d H:i:s');
            $result = Connection::table($this->table)
                ->where($this->primaryKey, $this->getKey())
                ->update(['deleted_at' => $this->attributes['deleted_at']]) > 0;
        } else {
            $result = Connection::table($this->table)
                ->where($this->primaryKey, $this->getKey())
                ->delete() > 0;
        }

        if ($result) {
            $this->fireEvent('deleted');
        }

        return $result;
    }

    public function forceDelete(): bool
    {
        return Connection::table($this->table)
            ->where($this->primaryKey, $this->getKey())
            ->delete() > 0;
    }

    public function restore(): bool
    {
        if (!$this->softDeletes) return false;
        $this->attributes['deleted_at'] = null;
        return Connection::table($this->table)
            ->where($this->primaryKey, $this->getKey())
            ->update(['deleted_at' => null]) > 0;
    }

    public function update(array $attributes): bool
    {
        $this->fill($attributes);
        return $this->save();
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    public function getKey(): mixed
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getDirty(): array
    {
        $dirty = [];
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }
        return $dirty;
    }

    public function getChanges(): array
    {
        $changes = [];
        foreach ($this->attributes as $key => $value) {
            if (array_key_exists($key, $this->original) && $this->original[$key] !== $value) {
                $changes[$key] = ['before' => $this->original[$key], 'after' => $value];
            }
        }
        return $changes;
    }

    public function setExists(bool $exists): void
    {
        $this->exists = $exists;
    }

    public function __get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    // --- Query scopes (static methods for query building) ---

    public static function query(): QueryBuilder
    {
        $instance = new static();
        return Connection::table($instance->table);
    }

    public static function find(mixed $id): ?static
    {
        $row = Connection::table((new static())->table)
            ->where((new static())->primaryKey, $id)
            ->first();
        return static::fromRow($row);
    }

    public static function findOrFail(mixed $id): static
    {
        $model = static::find($id);
        if (!$model) {
            throw new \RuntimeException(static::class . " no encontrada con ID: {$id}");
        }
        return $model;
    }

    public static function where(string $column, mixed $value): QueryBuilder
    {
        return static::query()->where($column, $value);
    }

    public static function all(): array
    {
        return static::fromRows(static::query()->get());
    }

    public static function create(array $data): static
    {
        $model = new static($data);
        $model->save();
        return $model;
    }

    public static function fromRow(?object $row): ?static
    {
        if (!$row) return null;
        $model = new static();
        foreach ((array) $row as $key => $value) {
            $model->attributes[$key] = $value;
        }
        $model->exists = true;
        $model->original = $model->attributes;
        return $model;
    }

    public static function fromRows(array $rows): array
    {
        return array_map(fn($row) => static::fromRow($row), $rows);
    }

    // --- Relationships ---

    public function belongsTo(string $related, string $foreignKey, string $ownerKey = 'id'): ?Model
    {
        $relatedClass = new $related();
        $row = Connection::table($relatedClass->table)
            ->where($ownerKey, $this->attributes[$foreignKey] ?? null)
            ->first();
        return $relatedClass::fromRow($row);
    }

    public function hasMany(string $related, string $foreignKey, string $localKey = 'id'): array
    {
        $relatedClass = new $related();
        $rows = Connection::table($relatedClass->table)
            ->where($foreignKey, $this->attributes[$localKey] ?? null)
            ->get();
        return $relatedClass::fromRows($rows);
    }

    public function belongsToMany(string $related, string $pivot, string $foreignPivotKey, string $relatedPivotKey, string $relatedKey = 'id', ?callable $queryFn = null): array
    {
        $relatedClass = new $related();
        $pivotRows = Connection::table($pivot)
            ->where($foreignPivotKey, $this->attributes['id'] ?? null)
            ->get();

        $ids = array_column((array) $pivotRows, $relatedPivotKey);
        if (empty($ids)) return [];

        $qb = Connection::table($relatedClass->table)->whereIn($relatedKey, $ids);
        if ($queryFn) {
            $qb = $queryFn($qb);
        }
        $rows = $qb->get();
        return $relatedClass::fromRows($rows);
    }

    public function morphMany(string $related, string $type, string $id): array
    {
        $relatedClass = new $related();
        $rows = Connection::table($relatedClass->table)
            ->where($type, static::class)
            ->where($id, $this->attributes['id'] ?? null)
            ->get();
        return $relatedClass::fromRows($rows);
    }

    public function morphToMany(string $related, string $relationName, string $relatedType = 'referenciable_type', string $relatedId = 'referenciable_id'): array
    {
        $relatedClass = new $related();
        $table = $relatedClass->table;

        $rows = Connection::table($relationName)
            ->where($relatedType, static::class)
            ->where($relatedId, $this->attributes['id'] ?? null)
            ->get();

        $idColumn = $related === 'App\\Models\\Etiqueta' ? 'etiqueta_id' : 'fuente_id';
        $ids = array_column((array) $rows, $idColumn);
        if (empty($ids)) return [];

        return $relatedClass::fromRows(
            Connection::table($table)->whereIn('id', $ids)->get()
        );
    }

    // --- Observer hooks ---

    private function fireEvent(string $event): void
    {
        $className = substr(strrchr(static::class, '\\') ?: static::class, 1);
        $observerClass = "App\\Observers\\{$className}Observer";

        if (class_exists($observerClass) && method_exists($observerClass, $event)) {
            $observerClass::$event($this);
        }
    }

    // --- Caching helpers ---

    public static function cached(string $key, int $ttl, callable $callback): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    public static function bustCache(string $key): void
    {
        Cache::forget($key);
    }

    // --- Soft delete scopes ---

    public static function withTrashed(): QueryBuilder
    {
        return static::query();
    }

    public static function onlyTrashed(): QueryBuilder
    {
        return static::query()->whereNotNull('deleted_at');
    }

    // --- Slug generation ---

    public static function generarSlug(string $texto): string
    {
        $slug = static::slugify($texto);
        $base = $slug;
        $i = 1;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public static function slugify(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        $text = str_replace(
            ['á','é','í','ó','ú','ñ','ü','ä','ë','ï','ö','â','ê','î','ô','û','à','è','ì','ò','ù'],
            ['a','e','i','o','u','n','u','a','e','i','o','a','e','i','o','u','a','e','i','o','u'],
            $text
        );
        $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}
