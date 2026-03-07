<?php

namespace App\Services;

use Illuminate\Support\Str;

class SearchQueryParser
{
    public function adjustOneQuoteOnly(string $value): string
    {
        $result = Str::replaceFirst('"', '', $value);
        if (Str::contains($result, '"')) {
            return $value;
        }

        return trim($result);
    }

    public function noSignal(string $value): bool
    {
        $operators = config('tes_constants.options.operadores');

        return mb_strlen($value, 'utf8') < 3 && ! in_array($value, $operators);
    }

    public function adjustOperators(string $keyword): string
    {
        if (in_array($keyword, ['OU', 'ou'])) {
            return 'OR';
        }

        if (in_array($keyword, ['E', 'e', 'MESMO', 'Mesmo', 'mesmo'])) {
            return 'AND';
        }

        if (in_array($keyword, ['NÃO', 'não', 'NAO', 'nao', 'Não', 'Nao'])) {
            return 'NOT';
        }

        return $keyword;
    }

    public function signalString(array $tokens, int $index): string
    {
        if ($this->noSignal($tokens[$index])) {
            return '';
        }

        if (empty($tokens[$index - 1])) {
            if (empty($tokens[$index + 1]) || $tokens[$index + 1] === 'OR') {
                return '';
            }

            return '+';
        }

        if ($tokens[$index - 1] === 'NOT') {
            return '-';
        }

        if ($tokens[$index - 1] === 'OR') {
            return '';
        }

        if ($tokens[$index - 1] === 'AND') {
            return '+';
        }

        return '';
    }

    public function keywordToArray(string $keyword): array
    {
        $keyword = $this->adjustOneQuoteOnly($keyword);
        $word = $keyword;
        $isFrase = false;
        $hasFrase = Str::contains($word, '"');
        $strings = explode(' ', $word);
        $phrase = '';
        $finalArray = [];

        foreach ($strings as $string) {
            if (trim($string) == '') {
                continue;
            }

            if (! $hasFrase) {
                $finalArray[] = $this->adjustOperators(trim($string));

                continue;
            }

            if (Str::startsWith($string, '"')) {
                if ($isFrase) {
                    $phrase .= '"';
                    $isFrase = false;
                    $finalArray[] = trim($phrase);
                    $phrase = '';

                    if (strlen(Str::of($string)->trim('"')) > 0) {
                        $finalArray[] = $this->adjustOperators((string) Str::of($string)->trim('"'));
                    }
                } else {
                    $isFrase = true;
                    $phrase .= Str::of($string)->trim().' ';

                    if (Str::endsWith($string, '"')) {
                        $isFrase = false;
                        $finalArray[] = trim($phrase);
                        $phrase = '';
                    }
                }
            } else {
                if ($isFrase) {
                    $phrase .= Str::of($string)->trim().' ';

                    if (Str::endsWith($string, '"')) {
                        $isFrase = false;
                        $finalArray[] = trim($phrase);
                        $phrase = '';
                    }
                } else {
                    if (Str::endsWith($string, '"')) {
                        $isFrase = true;
                        $phrase = '"';

                        if (strlen(Str::of($string)->trim('"')) > 0) {
                            $finalArray[] = $this->adjustOperators((string) Str::of($string)->trim('"'));
                        }
                    } else {
                        $finalArray[] = $this->adjustOperators((string) Str::of($string)->trim());
                    }
                }
            }
        }

        return array_filter($finalArray);
    }

    public function insertOperator(array $tokens): array
    {
        $operators = config('tes_constants.options.operadores');
        $newTokens = [];

        for ($index = 0; $index < count($tokens); $index++) {
            if ($index === 0) {
                $newTokens[] = $tokens[$index];

                continue;
            }

            if (! in_array($tokens[$index], $operators) && ! in_array($tokens[$index - 1], $operators)) {
                $newTokens[] = 'AND';
            }

            $newTokens[] = $tokens[$index];
        }

        return $newTokens;
    }

    public function buildFinalSearchString(array $tokens): string
    {
        $operators = config('tes_constants.options.operadores');
        $index = 0;
        $finalString = '';
        $parOpen = false;

        foreach ($tokens as $token) {
            if (! in_array($tokens[$index], $operators)) {
                $signal = $this->signalString($tokens, $index);

                if (! isset($lastOp)) {
                    $finalString .= "$signal{$tokens[$index]}";
                    $parOpen = true;
                } else {
                    if ($parOpen && $signal != $lastOp) {
                        $finalString .= " $signal{$tokens[$index]}";
                    } else {
                        $finalString .= " $signal{$tokens[$index]}";
                    }
                }

                $lastOp = $signal;
            }

            $index++;
        }

        return $finalString;
    }

    public function buildFinalSearchStringForApi(string $keyword, string $tribunal): string
    {
        if (in_array($tribunal, ['STF', 'TST'])) {
            $keyword = str_replace(' OU ', ' OR ', $keyword);
            $keyword = str_replace(' ou ', ' OR ', $keyword);
            $keyword = str_replace(' e ', ' AND ', $keyword);
            $keyword = str_replace(' E ', ' AND ', $keyword);
            $keyword = str_replace(' não ', ' NOT ', $keyword);
            $keyword = str_replace(' NÃO ', ' NOT ', $keyword);
            $keyword = str_replace(' nao ', ' NOT ', $keyword);
            $keyword = str_replace(' NAO ', ' NOT ', $keyword);
            $keyword = str_replace('/', '\\/', $keyword);
        }

        return $keyword;
    }
}
