<?php

declare(strict_types=1);

namespace Newla\Core\Http;

class JsonResponse extends Response
{
    public function __construct(mixed $data = [], int $status = 200, array $headers = [])
    {
        $headers['Content-Type'] = 'application/json; charset=UTF-8';
        $content = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        parent::__construct($content, $status, $headers);
    }

    public function setData(mixed $data): static
    {
        $this->content = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return $this;
    }
}