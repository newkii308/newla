<?php

declare(strict_types=1);

namespace Newla\Core\View;

use RuntimeException;

class ViewEngine
{
    protected string $basePath;
    protected array $sharedData = [];
    protected ?string $layout = null;
    protected array $sections = [];
    protected ?string $currentSection = null;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/\\');
    }

    public function share(string|array $key, mixed $value = null): void
    {
        if (is_array($key)) {
            $this->sharedData = array_merge($this->sharedData, $key);
        } else {
            $this->sharedData[$key] = $value;
        }
    }

    public function render(string $view, array $data = []): string
    {
        $viewPath = $this->resolvePath($view);
        if (!file_exists($viewPath)) {
            throw new RuntimeException("View [{$view}] not found at {$viewPath}");
        }

        $allData = array_merge($this->sharedData, $data);
        extract($allData, EXTR_SKIP);

        $this->layout = null;
        $this->sections = [];

        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        if ($this->layout !== null) {
            $layoutPath = $this->resolvePath($this->layout);
            if (!file_exists($layoutPath)) {
                throw new RuntimeException("Layout [{$this->layout}] not found at {$layoutPath}");
            }
            $this->sections['content'] = $this->sections['content'] ?? $content;
            ob_start();
            include $layoutPath;
            return ob_get_clean();
        }

        return $content;
    }

    public function layout(string $layout): void
    {
        $this->layout = $layout;
    }

    public function section(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }

    public function endSection(): void
    {
        if ($this->currentSection === null) {
            throw new RuntimeException('No section started.');
        }
        $this->sections[$this->currentSection] = ob_get_clean();
        $this->currentSection = null;
    }

    public function yield(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    public function include(string $view, array $data = []): void
    {
        $viewPath = $this->resolvePath($view);
        if (!file_exists($viewPath)) {
            throw new RuntimeException("Partial view [{$view}] not found at {$viewPath}");
        }
        $allData = array_merge($this->sharedData, $data);
        extract($allData, EXTR_SKIP);
        include $viewPath;
    }

    public function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    protected function resolvePath(string $view): string
    {
        $normalized = str_replace('.', DIRECTORY_SEPARATOR, $view);
        return $this->basePath . DIRECTORY_SEPARATOR . $normalized . '.php';
    }
}