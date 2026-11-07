<?php

namespace App\Services\Search;

/**
 * Ce que dit une phrase : mots utiles, ville, prix.
 * Ville et prix ne servent qu'à classer : ils n'excluent jamais une annonce.
 */
class QueryIntent
{
    private const NUMBER = '(\d{1,3}(?: \d{3})+|\d+)( ?k| ?mille)?';
    private const CURRENCY = '(?: (?:f|fr|fcfa|cfa|franc|francs|xof))?';

    /** @param  list<string>  $words */
    public function __construct(
        public readonly array $words,
        public readonly ?string $ville = null,
        public readonly ?float $prixMin = null,
        public readonly ?float $prixMax = null,
        public readonly bool $prixBas = false,
    ) {
    }

    /** @param  list<string>  $cities  villes normalisées, connues par les annonces */
    public static function parse(string $normalized, array $cities, TextNormalizer $text): self
    {
        $query = " {$normalized} ";
        $min = $max = null;

        $query = (string) preg_replace('/ (pas cher|pas chere|pas chers|pas cheres|bon marche|moins cher|petit prix|prix bas) /', ' ', $query, -1, $cheap);

        $number = self::NUMBER;
        $currency = self::CURRENCY;

        if (preg_match("/ entre {$number}{$currency} et {$number}{$currency} /", $query, $match)) {
            [$min, $max] = [self::amount($match[1], $match[2]), self::amount($match[3], $match[4] ?? '')];
            $query = str_replace($match[0], ' ', $query);
        } elseif (preg_match("/ (?:moins de|max|maximum|budget|jusqu a|pas plus de) {$number}{$currency} /", $query, $match)) {
            $max = self::amount($match[1], $match[2] ?? '');
            $query = str_replace($match[0], ' ', $query);
        } elseif (preg_match("/ (?:plus de|min|minimum|a partir de) {$number}{$currency} /", $query, $match)) {
            $min = self::amount($match[1], $match[2] ?? '');
            $query = str_replace($match[0], ' ', $query);
        } elseif (preg_match('/ (\d{2,4}) ?k /', $query, $match)) {
            // À partir de 10k : en dessous, c'est souvent un modèle (« tv 4k »).
            $max = (float) $match[1] * 1000;
            $query = str_replace($match[0], ' ', $query);
        }

        $ville = null;
        usort($cities, fn ($a, $b) => strlen($b) <=> strlen($a));
        foreach ($cities as $city) {
            if ($city !== '' && str_contains($query, " {$city} ")) {
                $ville = $city;
                $query = str_replace(" {$city} ", ' ', $query);
                break;
            }
        }

        $words = array_values(array_filter(
            preg_split('/\s+/', trim($query)) ?: [],
            fn ($word) => $word !== '' && ! $text->isStopWord($word)
        ));

        return new self($words, $ville, $min, $max, $cheap > 0);
    }

    public function hasCriteria(): bool
    {
        return $this->ville !== null || $this->prixMin !== null || $this->prixMax !== null || $this->prixBas;
    }

    private static function amount(string $digits, string $unit): float
    {
        return (float) str_replace(' ', '', $digits) * (trim($unit) === '' ? 1 : 1000);
    }
}
