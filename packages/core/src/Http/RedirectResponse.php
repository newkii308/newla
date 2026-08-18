<?php

declare(strict_types=1);

namespace Newla\Core\Http;

class RedirectResponse extends Response
{
    protected string $targetUrl;

    public function __construct(string $url, int $status = 302, array $headers = [])
    {
        $this->targetUrl = $url;
        $headers['Location'] = $url;
        parent::__construct('', $status, $headers);
    }

    public function getTargetUrl(): string
    {
        return $this->targetUrl;
    }
}