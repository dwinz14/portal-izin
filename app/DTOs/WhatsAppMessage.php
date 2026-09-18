<?php

namespace App\DTOs;

class WhatsAppMessage
{
    private string $content  = '';
    private array  $metadata = [];

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    /** Factory method untuk chaining yang lebih bersih. */
    public static function create(string $content = ''): static
    {
        return new static($content);
    }

    public function content(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function withMetadata(array $metadata): static
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
