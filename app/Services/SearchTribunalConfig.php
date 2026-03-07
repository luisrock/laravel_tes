<?php

namespace App\Services;

class SearchTribunalConfig
{
    public function __construct(
        protected string $tribunalUpper,
        protected array $config,
    ) {}

    public static function fromArray(string $tribunalUpper, array $config): self
    {
        return new self($tribunalUpper, $config);
    }

    public function tribunalUpper(): string
    {
        return $this->tribunalUpper;
    }

    public function tribunalLower(): string
    {
        return strtolower($this->tribunalUpper);
    }

    public function usesDatabase(): bool
    {
        return (bool) $this->config['db'];
    }

    public function teseName(): string
    {
        return $this->config['tese_name'];
    }

    public function tables(): array
    {
        return $this->config['tables'];
    }

    public function matchColumnsFor(string $itemType): string
    {
        return $this->config["to_match_{$itemType}"] ?? '';
    }

    public function toArray(): array
    {
        return $this->config;
    }
}
