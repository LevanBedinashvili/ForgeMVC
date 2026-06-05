<?php

declare(strict_types=1);

namespace Core;

class Model
{
    /**
     * The database table associated with this model.
     * Override in child models (e.g., protected string $table = 'posts';).
     */
    protected string $table = '';

    /**
     * The primary key column name.
     */
    protected string $primaryKey = 'id';

    /**
     * Indicates if the model's primary key is auto-incrementing.
     */
    public bool $incrementing = true;

    /**
     * Dynamic attributes storage.
     * Populated via __set, or when fetching with PDO::FETCH_CLASS.
     */
    protected array $attributes = [];

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = ['*'];

    /**
     * Track whether this model instance exists in the database.
     * Set to true when loaded via find() or all().
     */
    public bool $exists = false;

    /**
     * Magic setter — store dynamic attributes.
     */
    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Magic getter — retrieve dynamic attributes.
     */
    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Check if a dynamic attribute is set.
     */
    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Fill the model with an array of attributes.
     */
    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Determine if the given attribute may be mass assigned.
     */
    protected function isFillable(string $key): bool
    {
        if (in_array($key, $this->fillable, true)) {
            return true;
        }

        if ($this->guarded === ['*'] || in_array($key, $this->guarded, true)) {
            return false;
        }

        return empty($this->fillable);
    }

    /**
     * Validate column names to prevent SQL Injection.
     */
    protected function validateColumn(string $column): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            throw new \InvalidArgumentException("Invalid column name: {$column}");
        }
    }

    /**
     * Save the model (INSERT or UPDATE).
     */
    public function save(): bool
    {
        $pdo = Database::getConnection();

        if ($this->exists) {
            // UPDATE
            $columns = [];
            $values = [];
            foreach ($this->attributes as $key => $value) {
                if ($key === $this->primaryKey) {
                    continue;
                }
                $this->validateColumn($key);
                $columns[] = "`{$key}` = :{$key}";
                $values[$key] = $value;
            }

            if (empty($columns)) {
                return true; // Nothing to update
            }

            $sql = "UPDATE {$this->table} SET " . implode(', ', $columns)
                 . " WHERE `{$this->primaryKey}` = :pk_value";
            $values['pk_value'] = $this->attributes[$this->primaryKey];

            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            return $stmt->rowCount() > 0;
        }

        // INSERT
        $columns = [];
        $placeholders = [];
        $values = [];
        foreach ($this->attributes as $key => $value) {
            $this->validateColumn($key);
            $columns[] = "`{$key}`";
            $placeholders[] = ":{$key}";
            $values[$key] = $value;
        }

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") "
             . "VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($values);

        if ($result) {
            if ($this->incrementing && !isset($this->attributes[$this->primaryKey])) {
                $this->attributes[$this->primaryKey] = $pdo->lastInsertId();
            }
            $this->exists = true;
        }

        return $result;
    }

    /**
     * Fetch all records from the table as model instances.
     * Defaults to limit 1000 to prevent memory exhaustion.
     *
     * @return static[]
     */
    public static function all(int $limit = 1000, int $offset = 0): array
    {
        $model = new static();
        $pdo = Database::getConnection();

        $sql = "SELECT * FROM {$model->table} LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_CLASS, static::class);

        foreach ($results as $instance) {
            $instance->exists = true;
        }

        return $results;
    }

    /**
     * Find a single record by its primary key.
     */
    public static function find(int|string $id): ?static
    {
        $model = new static();
        $pdo = Database::getConnection();

        $sql = "SELECT * FROM {$model->table} WHERE `{$model->primaryKey}` = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $instance = $stmt->fetchObject(static::class);

        if ($instance === false) {
            return null;
        }

        $instance->exists = true;
        return $instance;
    }

    /**
     * Execute a custom query and return an array of model instances.
     * This correctly hydrates the $exists flag.
     *
     * @return static[]
     */
    public static function query(string $sql, array $params = []): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $results = $stmt->fetchAll(\PDO::FETCH_CLASS, static::class);

        foreach ($results as $instance) {
            $instance->exists = true;
        }

        return $results;
    }

    /**
     * Delete the current record from the database.
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $pdo = Database::getConnection();

        $sql = "DELETE FROM {$this->table} WHERE `{$this->primaryKey}` = :id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute(['id' => $this->attributes[$this->primaryKey]]);

        if ($result) {
            $this->exists = false;
        }

        return $result;
    }

    /**
     * Get all attributes as an array.
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Get the table name.
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Define a one-to-many relationship.
     */
    public function hasMany(string $relatedClass, ?string $foreignKey = null, ?string $localKey = null): array
    {
        /** @var Model $relatedModel */
        $relatedModel = new $relatedClass();
        
        // Guess the foreign key if null (e.g. App\Models\User -> user_id)
        $className = basename(str_replace('\\', '/', static::class));
        $foreignKey = $foreignKey ?? strtolower($className) . '_id';
        $localKey = $localKey ?? $this->primaryKey;

        $sql = "SELECT * FROM {$relatedModel->getTable()} WHERE `{$foreignKey}` = :value";
        return $relatedClass::query($sql, ['value' => $this->attributes[$localKey] ?? null]);
    }

    /**
     * Define an inverse one-to-one or many relationship.
     */
    public function belongsTo(string $relatedClass, ?string $foreignKey = null, ?string $ownerKey = null): ?Model
    {
        /** @var Model $relatedModel */
        $relatedModel = new $relatedClass();
        
        // Guess the foreign key if null (e.g. App\Models\Post -> post_id)
        $className = basename(str_replace('\\', '/', $relatedClass));
        $foreignKey = $foreignKey ?? strtolower($className) . '_id';
        $ownerKey = $ownerKey ?? $relatedModel->primaryKey;

        $sql = "SELECT * FROM {$relatedModel->getTable()} WHERE `{$ownerKey}` = :value LIMIT 1";
        $results = $relatedClass::query($sql, ['value' => $this->attributes[$foreignKey] ?? null]);
        
        return $results[0] ?? null;
    }
}
