<?php

declare(strict_types=1);

namespace Newla\Cli\Output;

class ConsoleOutput
{
    protected bool $hasColorSupport;

    public function __construct()
    {
        $this->hasColorSupport = $this->checkColorSupport();
    }

    protected function checkColorSupport(): bool
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return getenv('ANSICON') !== false
                || getenv('ConEmuANSI') === 'ON'
                || getenv('TERM') === 'xterm'
                || (function_exists('sapi_windows_vt100_support') && @sapi_windows_vt100_support(STDOUT));
        }

        return function_exists('posix_isatty') && @posix_isatty(STDOUT);
    }

    public function write(string $message): void
    {
        echo $message;
    }

    public function writeln(string $message = ''): void
    {
        echo $message . PHP_EOL;
    }

    public function success(string $message): void
    {
        $this->writeln($this->color("✓ {$message}", '32'));
    }

    public function error(string $message): void
    {
        $this->writeln($this->color("✗ {$message}", '31'));
    }

    public function warning(string $message): void
    {
        $this->writeln($this->color("⚠ {$message}", '33'));
    }

    public function info(string $message): void
    {
        $this->writeln($this->color("ℹ {$message}", '36'));
    }

    public function line(string $message): void
    {
        $this->writeln($message);
    }

    public function title(string $title): void
    {
        $this->writeln();
        $this->writeln($this->color("=== {$title} ===", '1;34'));
        $this->writeln();
    }

    public function color(string $text, string $code): string
    {
        if (!$this->hasColorSupport) {
            return $text;
        }
        return "\033[{$code}m{$text}\033[0m";
    }

    public function banner(): void
    {
        $banner = <<<TXT
\033[1;36m
 _   _  _____ _    _ _               
| \ | || ____\ \  / / |        /\    
|  \| || |__  \ \/ /| |       /  \   
| . ` ||  __|  \  / | |      / /\ \  
| |\  || |___   \/  | |____ / ____ \ 
|_| \_||_____|      |______/_/    \_\
\033[0m\033[37mModern, Native PHP Toolkit & Framework v1.0.0\033[0m
TXT;
        $this->writeln($this->hasColorSupport ? $banner : "NEWLA Developer Toolkit v1.0.0");
        $this->writeln();
    }
}