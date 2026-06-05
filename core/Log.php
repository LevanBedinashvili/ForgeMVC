<?php

declare(strict_types=1);

namespace Core;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;

class Log
{
    protected static ?Logger $logger = null;

    /**
     * Get the Monolog logger instance.
     */
    public static function getLogger(): Logger
    {
        if (static::$logger === null) {
            static::$logger = new Logger('forge');
            
            $logPath = BASE_PATH . 'storage/logs/app.log';
            
            // Ensure logs directory exists
            $logDir = dirname($logPath);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            static::$logger->pushHandler(new StreamHandler($logPath, Level::Debug));
        }

        return static::$logger;
    }

    public static function info(string $message, array $context = []): void
    {
        static::getLogger()->info($message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        static::getLogger()->warning($message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        static::getLogger()->error($message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        static::getLogger()->debug($message, $context);
    }
}
