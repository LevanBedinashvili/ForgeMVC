<?php

declare(strict_types=1);

namespace Core;

use Core\Contracts\JobInterface;

class Queue
{
    /**
     * Push a new job onto the queue.
     */
    public static function push(JobInterface $job): void
    {
        $pdo = Database::getConnection();
        $payload = serialize($job);

        $sql = "INSERT INTO jobs (payload, attempts, status, created_at) VALUES (:payload, 0, 'pending', CURRENT_TIMESTAMP)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['payload' => $payload]);
    }
}
