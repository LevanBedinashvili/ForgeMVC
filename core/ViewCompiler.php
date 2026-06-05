<?php

declare(strict_types=1);

namespace Core;

class ViewCompiler
{
    protected string $viewPath;
    protected string $cachePath;

    public function __construct()
    {
        $this->viewPath = BASE_PATH . 'views/';
        $this->cachePath = BASE_PATH . 'storage/views/';
    }

    /**
     * Compile the given view file if necessary, and return the path to the compiled PHP file.
     *
     * @param string $path The absolute path to the .forge.php view file.
     * @return string The absolute path to the compiled .php file.
     */
    public function compile(string $path): string
    {
        // Generate a unique hash for the cached filename
        $cacheFilename = md5($path) . '.php';
        $compiledPath = $this->cachePath . $cacheFilename;

        // Check if we need to recompile
        if (!file_exists($compiledPath) || filemtime($path) > filemtime($compiledPath)) {
            $content = file_get_contents($path);
            if ($content === false) {
                throw new \RuntimeException("Could not read view file: {$path}");
            }

            $compiled = $this->compileContent($content);

            // Ensure cache directory exists
            if (!is_dir($this->cachePath)) {
                mkdir($this->cachePath, 0755, true);
            }

            file_put_contents($compiledPath, $compiled);
        }

        return $compiledPath;
    }

    /**
     * Perform regex replacements on the view content.
     */
    protected function compileContent(string $content): string
    {
        // Compile unescaped tags: {!! $var !!} -> <?= $var ? >
        $content = preg_replace('/\{!!\s*(.+?)\s*!!\}/s', '<?= $1 ?>', $content);

        // Compile escaped tags: {{ $var }} -> <?= e($var) ? >
        $content = preg_replace('/\{\{\s*(.+?)\s*\}\}/s', '<?= e($1) ?>', $content);

        return $content;
    }
}
