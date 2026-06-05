<?php

declare(strict_types=1);

namespace Core;

class Controller
{
    /**
     * Default layout file (relative to views/layouts/).
     * Set to null in a child controller to disable layouts.
     */
    protected ?string $layout = 'main';

    /**
     * Render a view file with optional data, wrapped in a layout.
     *
     * Usage from a child controller:
     *   return $this->render('home/index', ['title' => 'Welcome']);
     *
     * This will:
     *   1. Extract $data as local variables in the view scope
     *   2. Use output buffering to capture the view's HTML
     *   3. Inject the captured HTML into the layout as $content
     *
     * @param string $view  Path relative to views/ (e.g., 'home/index' → views/home/index.php)
     * @param array  $data  Associative array of variables to pass to the view
     */
    public function render(string $view, array $data = []): string
    {
        $viewPath = BASE_PATH . 'views/' . $view . '.php';
        $forgeViewPath = BASE_PATH . 'views/' . $view . '.forge.php';

        if (file_exists($forgeViewPath)) {
            $realViewPath = realpath($forgeViewPath);
            $isForge = true;
        } else {
            $realViewPath = realpath($viewPath);
            $isForge = false;
        }

        $baseViewsDir = realpath(BASE_PATH . 'views');

        // Prevent LFI / Path Traversal
        if ($realViewPath === false || !str_starts_with($realViewPath, $baseViewsDir)) {
            throw new \RuntimeException("View [{$view}] not found or access denied.", 403);
        }

        if ($isForge) {
            $compiler = new ViewCompiler();
            $fileToRender = $compiler->compile($realViewPath);
        } else {
            $fileToRender = $realViewPath;
        }

        // Capture the view content using output buffering
        $content = $this->renderFile($fileToRender, $data);

        // If a layout is set, wrap the view content inside it
        if ($this->layout !== null) {
            $layoutPath = BASE_PATH . 'views/layouts/' . $this->layout . '.php';
            $forgeLayoutPath = BASE_PATH . 'views/layouts/' . $this->layout . '.forge.php';

            if (file_exists($forgeLayoutPath)) {
                $realLayoutPath = realpath($forgeLayoutPath);
                $isForgeLayout = true;
            } else {
                $realLayoutPath = realpath($layoutPath);
                $isForgeLayout = false;
            }

            // Prevent LFI / Path Traversal for layouts
            if ($realLayoutPath === false || !str_starts_with($realLayoutPath, $baseViewsDir)) {
                throw new \RuntimeException("Layout [{$this->layout}] not found or access denied.", 403);
            }

            if ($isForgeLayout) {
                $compiler = new ViewCompiler();
                $layoutToRender = $compiler->compile($realLayoutPath);
            } else {
                $layoutToRender = $realLayoutPath;
            }

            // Pass the captured view content to the layout as $content
            $content = $this->renderFile($layoutToRender, array_merge($data, ['content' => $content]));
        }

        return $content;
    }

    /**
     * Render a PHP file with extracted data using output buffering.
     *
     * @param string $filePath Absolute path to the PHP file
     * @param array  $data     Variables to extract into the file's scope
     */
    protected function renderFile(string $filePath, array $data = []): string
    {
        // Extract data array into individual variables for use in the view
        extract($data, EXTR_SKIP);

        ob_start();
        require $filePath;
        return ob_get_clean();
    }
}
