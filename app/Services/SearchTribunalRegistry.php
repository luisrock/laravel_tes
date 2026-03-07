<?php

namespace App\Services;

class SearchTribunalRegistry
{
    /**
     * @return array<string, array>
     */
    public function allRaw(): array
    {
        return config('tes_constants.lista_tribunais', []);
    }

    /**
     * @return array<string, SearchTribunalConfig>
     */
    public function all(): array
    {
        $configs = [];

        foreach ($this->allRaw() as $tribunalUpper => $config) {
            $configs[$tribunalUpper] = SearchTribunalConfig::fromArray($tribunalUpper, $config);
        }

        return $configs;
    }

    public function get(string $tribunalUpper): SearchTribunalConfig
    {
        $tribunalUpper = strtoupper($tribunalUpper);
        $config = $this->allRaw()[$tribunalUpper] ?? null;

        if (! is_array($config)) {
            throw new \InvalidArgumentException("Tribunal config not found for {$tribunalUpper}");
        }

        return SearchTribunalConfig::fromArray($tribunalUpper, $config);
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        return array_keys($this->allRaw());
    }

    /**
     * @return array<string, SearchTribunalConfig>
     */
    public function databaseEnabled(): array
    {
        return array_filter($this->all(), fn (SearchTribunalConfig $config): bool => $config->usesDatabase());
    }
}
