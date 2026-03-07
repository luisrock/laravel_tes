<?php

namespace App\Services;

class SearchResultSection
{
    public function __construct(
        protected string $name,
        protected int $total = 0,
        protected array $hits = [],
    ) {}

    public static function empty(string $name): self
    {
        return new self($name);
    }

    public static function fromArray(string $name, array $data): self
    {
        $hits = $data['hits'] ?? [];

        if (! is_array($hits)) {
            $hits = [];
        }

        return new self(
            $name,
            isset($data['total']) ? (int) $data['total'] : count($hits),
            $hits,
        );
    }

    public function addHits(array $hits): void
    {
        $this->hits = array_merge($this->hits, $hits);
        $this->total = count($this->hits);
    }

    public function total(): int
    {
        return $this->total;
    }

    public function hits(): array
    {
        return $this->hits;
    }

    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'hits' => $this->hits,
        ];
    }
}
