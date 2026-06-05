<?php

declare(strict_types=1);

namespace Core;

class Migrator
{
    protected \PDO $pdo;
    protected string $migrationsPath;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->migrationsPath = BASE_PATH . 'database/migrations/';
    }

    public function migrate(): void
    {
        $this->createMigrationsTable();

        $appliedMigrations = $this->getAppliedMigrations();
        $files = scandir($this->migrationsPath);
        $migrationsToApply = array_diff($files, $appliedMigrations);

        $appliedCount = 0;
        foreach ($migrationsToApply as $migration) {
            if ($migration === '.' || $migration === '..') {
                continue;
            }

            if (pathinfo($migration, PATHINFO_EXTENSION) === 'php') {
                $this->applyMigration($migration);
                $appliedCount++;
            }
        }

        if ($appliedCount === 0) {
            echo "Nothing to migrate. All migrations are applied.\n";
        }
    }

    protected function createMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->pdo->exec($sql);
    }

    protected function getAppliedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT migration FROM migrations");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    protected function applyMigration(string $migration): void
    {
        $path = $this->migrationsPath . $migration;
        $instance = require $path;

        if (is_object($instance) && method_exists($instance, 'up')) {
            echo "Applying migration: {$migration}\n";
            $instance->up($this->pdo);

            $stmt = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
            $stmt->execute(['migration' => $migration]);

            echo "Applied migration: {$migration}\n";
        } else {
            echo "Warning: Migration file {$migration} must return an object with an 'up' method.\n";
        }
    }
}
