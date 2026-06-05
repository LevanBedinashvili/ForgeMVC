<?php

declare(strict_types=1);

namespace Core;

class Container
{
    /**
     * Manually registered bindings.
     * @var array<string, callable>
     */
    protected static array $bindings = [];

    /**
     * Singleton instances.
     */
    protected static array $instances = [];

    /**
     * Stack of classes currently being resolved to detect circular dependencies.
     */
    protected static array $resolving = [];

    /**
     * Register a binding in the container.
     */
    public static function bind(string $abstract, callable $factory): void
    {
        static::$bindings[$abstract] = $factory;
    }

    /**
     * Register a singleton binding in the container.
     */
    public static function singleton(string $abstract, callable $factory): void
    {
        static::bind($abstract, function () use ($abstract, $factory) {
            if (!isset(static::$instances[$abstract])) {
                static::$instances[$abstract] = call_user_func($factory);
            }
            return static::$instances[$abstract];
        });
    }

    /**
     * Resolve a class from the container.
     */
    public static function resolve(string $class): object
    {
        // Check for a manual binding first
        if (isset(static::$bindings[$class])) {
            return call_user_func(static::$bindings[$class]);
        }

        if (in_array($class, static::$resolving, true)) {
            throw new \RuntimeException("Circular dependency detected while resolving [{$class}].");
        }

        static::$resolving[] = $class;

        try {
            // Use Reflection to auto-wire
            $reflection = new \ReflectionClass($class);

            if (!$reflection->isInstantiable()) {
                throw new \RuntimeException("Cannot instantiate [{$class}]. It may be abstract or an interface.");
            }

            $constructor = $reflection->getConstructor();

            // No constructor — just instantiate
            if ($constructor === null) {
                return $reflection->newInstance();
            }

            // Inspect constructor parameters
            $parameters = $constructor->getParameters();
            $dependencies = [];

            foreach ($parameters as $param) {
                $type = $param->getType();

                // If the parameter has no type hint, check for a default value
                if ($type === null || $type->isBuiltin()) {
                    if ($param->isDefaultValueAvailable()) {
                        $dependencies[] = $param->getDefaultValue();
                    } else {
                        throw new \RuntimeException(
                            "Cannot resolve parameter [\${$param->getName()}] in [{$class}]: no type hint and no default value."
                        );
                    }
                    continue;
                }

                // Recursively resolve the type-hinted class
                $dependencies[] = static::resolve($type->getName());
            }

            return $reflection->newInstanceArgs($dependencies);
        } finally {
            array_pop(static::$resolving);
        }
    }
}
