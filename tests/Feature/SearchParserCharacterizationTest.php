<?php

use App\Services\SearchQueryParser;

function parserTokens(string $input): array
{
    return array_map(static fn ($token) => (string) $token, keyword_to_array($input));
}

function parserTokensWithImplicitOperators(string $input): array
{
    return array_map(static fn ($token) => (string) $token, insertOperator(keyword_to_array($input)));
}

function parserFinalString(string $input): string
{
    return buildFinalSearchString(insertOperator(keyword_to_array($input)));
}

function searchQueryParser(): SearchQueryParser
{
    return app(SearchQueryParser::class);
}

dataset('parser final strings', [
    'implicit and' => ['dano moral', ['dano', 'moral'], ['dano', 'AND', 'moral'], '+dano +moral'],
    'explicit and' => ['dano E moral', ['dano', 'AND', 'moral'], ['dano', 'AND', 'moral'], '+dano +moral'],
    'explicit or' => ['dano OU moral', ['dano', 'OR', 'moral'], ['dano', 'OR', 'moral'], 'dano moral'],
    'explicit not' => ['dano não moral', ['dano', 'NOT', 'moral'], ['dano', 'NOT', 'moral'], '+dano -moral'],
    'quoted phrase' => ['"dano moral" responsabilidade', ['"dano moral"', 'responsabilidade'], ['"dano moral"', 'AND', 'responsabilidade'], '+"dano moral" +responsabilidade'],
    'uppercase acronym with and' => ['ICMS e PIS', ['ICMS', 'AND', 'PIS'], ['ICMS', 'AND', 'PIS'], '+ICMS +PIS'],
    'short terms remain unsignaled' => ['a b dano', ['a', 'b', 'dano'], ['a', 'AND', 'b', 'AND', 'dano'], 'a b +dano'],
    'mesmo is a regular search term' => ['mesmo tema', ['mesmo', 'tema'], ['mesmo', 'AND', 'tema'], '+mesmo +tema'],
    'Ou titlecase becomes OR' => ['ICMS Ou PIS', ['ICMS', 'OR', 'PIS'], ['ICMS', 'OR', 'PIS'], 'ICMS PIS'],
    'number token remains searchable' => ['sumula 123', ['sumula', '123'], ['sumula', 'AND', '123'], '+sumula +123'],
]);

it('characterizes parser tokenization and final search string', function (string $input, array $expectedTokens, array $expectedImplicitTokens, string $expectedFinalString) {
    expect(parserTokens($input))->toBe($expectedTokens)
        ->and(parserTokensWithImplicitOperators($input))->toBe($expectedImplicitTokens)
        ->and(parserFinalString($input))->toBe($expectedFinalString);
})->with('parser final strings');

it('preserves the same parser behavior through the dedicated class', function (string $input, array $expectedTokens, array $expectedImplicitTokens, string $expectedFinalString) {
    $parser = searchQueryParser();

    expect(array_map(static fn ($token) => (string) $token, $parser->keywordToArray($input)))->toBe($expectedTokens)
        ->and(array_map(static fn ($token) => (string) $token, $parser->insertOperator($parser->keywordToArray($input))))->toBe($expectedImplicitTokens)
        ->and($parser->buildFinalSearchString($parser->insertOperator($parser->keywordToArray($input))))->toBe($expectedFinalString);
})->with('parser final strings');

it('removes a lone quote before tokenizing', function () {
    expect(adjustOneQuoteOnly('"dano moral'))->toBe('dano moral')
        ->and(parserFinalString('"dano moral'))->toBe('+dano +moral');
});

it('normalizes API operators for STF requests', function () {
    expect(buildFinalSearchStringForApi('dano OU moral/culpa não grave', 'STF'))
        ->toBe('dano OR moral\/culpa NOT grave');
});

it('normalizes API operators through the dedicated parser class', function () {
    expect(searchQueryParser()->buildFinalSearchStringForApi('dano OU moral/culpa não grave', 'STF'))
        ->toBe('dano OR moral\/culpa NOT grave');
});

it('does not normalize API operators for tribunals outside the special-case list', function () {
    expect(buildFinalSearchStringForApi('dano OU moral', 'STJ'))
        ->toBe('dano OU moral');
});
