<?php

namespace App\Services;

class SearchTribunalResult
{
    /**
     * @param  array<string, SearchResultSection>  $sections
     */
    public function __construct(
        protected string $teseSectionName,
        protected array $sections,
        protected int $totalCount = 0,
    ) {}

    public static function empty(string $teseSectionName): self
    {
        return new self($teseSectionName, [
            'sumula' => SearchResultSection::empty('sumula'),
            $teseSectionName => SearchResultSection::empty($teseSectionName),
        ]);
    }

    public static function fromArray(string $teseSectionName, array $data): self
    {
        $sections = [];

        foreach ($data as $key => $value) {
            if ($key === 'total_count' || ! is_array($value)) {
                continue;
            }

            $sections[$key] = SearchResultSection::fromArray($key, $value);
        }

        if (! isset($sections['sumula'])) {
            $sections['sumula'] = SearchResultSection::empty('sumula');
        }

        if (! isset($sections[$teseSectionName])) {
            $sections[$teseSectionName] = SearchResultSection::empty($teseSectionName);
        }

        $totalCount = isset($data['total_count'])
            ? (int) $data['total_count']
            : array_sum(array_map(
                static fn (SearchResultSection $section): int => $section->total(),
                $sections,
            ));

        return new self($teseSectionName, $sections, $totalCount);
    }

    public function addHits(string $sectionName, array $hits): void
    {
        if (! isset($this->sections[$sectionName])) {
            $this->sections[$sectionName] = SearchResultSection::empty($sectionName);
        }

        $this->sections[$sectionName]->addHits($hits);
        $this->recalculateTotalCount();
    }

    public function sumula(): SearchResultSection
    {
        return $this->section('sumula');
    }

    public function tese(): SearchResultSection
    {
        return $this->section($this->teseSectionName);
    }

    public function section(string $sectionName): SearchResultSection
    {
        return $this->sections[$sectionName] ?? SearchResultSection::empty($sectionName);
    }

    public function totalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @return array{total_sum:int,total_rep:int,hits_sum:array,hits_rep:array}
     */
    public function toPublicApiArray(): array
    {
        return [
            'total_sum' => $this->sumula()->total(),
            'total_rep' => $this->tese()->total(),
            'hits_sum' => $this->sumula()->hits(),
            'hits_rep' => $this->tese()->hits(),
        ];
    }

    /**
     * @return array{sumulas:int,teses:int,total:int}
     */
    public function toUnifiedSummaryArray(): array
    {
        return [
            'sumulas' => $this->sumula()->total(),
            'teses' => $this->tese()->total(),
            'total' => $this->totalCount(),
        ];
    }

    public function toArray(): array
    {
        $output = [];

        foreach ($this->sections as $sectionName => $section) {
            $output[$sectionName] = $section->toArray();
        }

        $output['total_count'] = $this->totalCount;

        return $output;
    }

    private function recalculateTotalCount(): void
    {
        $this->totalCount = array_sum(array_map(
            static fn (SearchResultSection $section): int => $section->total(),
            $this->sections,
        ));
    }
}
